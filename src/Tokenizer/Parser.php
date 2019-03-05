<?php declare(strict_types = 1);

namespace Apicart\FQL\Tokenizer;

use Apicart\FQL\Contract\Parser\ParserInterface;
use Apicart\FQL\Token\Node\Group;
use Apicart\FQL\Token\Node\LogicalAnd;
use Apicart\FQL\Token\Node\LogicalNot;
use Apicart\FQL\Token\Node\LogicalOr;
use Apicart\FQL\Token\Node\Mandatory;
use Apicart\FQL\Token\Node\Prohibited;
use Apicart\FQL\Token\Node\Query;
use Apicart\FQL\Token\Node\Term;
use Apicart\FQL\Value\AbstractNode;
use Apicart\FQL\Value\Correction;
use Apicart\FQL\Value\SyntaxTree;
use Apicart\FQL\Value\Token;
use Apicart\FQL\Value\TokenSequence;
use SplStack;

final class Parser implements ParserInterface
{

	/**
	 * Parser ignored adjacent unary operator preceding another operator.
	 */
	public const CORRECTION_ADJACENT_UNARY_OPERATOR_PRECEDING_OPERATOR_IGNORED = 0;

	/**
	 * Parser ignored unary operator missing an operand.
	 */
	public const CORRECTION_UNARY_OPERATOR_MISSING_OPERAND_IGNORED = 1;

	/**
	 * Parser ignored binary operator missing left side operand.
	 */
	public const CORRECTION_BINARY_OPERATOR_MISSING_LEFT_OPERAND_IGNORED = 2;

	/**
	 * Parser ignored binary operator missing right side operand.
	 */
	public const CORRECTION_BINARY_OPERATOR_MISSING_RIGHT_OPERAND_IGNORED = 3;

	/**
	 * Parser ignored binary operator following another operator and connecting operators.
	 */
	public const CORRECTION_BINARY_OPERATOR_FOLLOWING_OPERATOR_IGNORED = 4;

	/**
	 * Parser ignored logical not operators preceding mandatory or prohibited operator.
	 */
	public const CORRECTION_LOGICAL_NOT_OPERATORS_PRECEDING_PREFERENCE_IGNORED = 5;

	/**
	 * Parser ignored empty group and connecting operators.
	 */
	public const CORRECTION_EMPTY_GROUP_IGNORED = 6;

	/**
	 * Parser ignored unmatched left side group delimiter.
	 */
	public const CORRECTION_UNMATCHED_GROUP_LEFT_DELIMITER_IGNORED = 7;

	/**
	 * Parser ignored unmatched right side group delimiter.
	 */
	public const CORRECTION_UNMATCHED_GROUP_RIGHT_DELIMITER_IGNORED = 8;

	/**
	 * Parser ignored bailout type token.
	 *
	 * @see Tokenizer::TOKEN_BAILOUT
	 */
	public const CORRECTION_BAILOUT_TOKEN_IGNORED = 9;

	/**
	 * @var array
	 */
	private static $reductionGroups = [
		'group' => ['reduceGroup', 'reducePreference', 'reduceLogicalNot', 'reduceLogicalAnd', 'reduceLogicalOr'],
		'unaryOperator' => ['reduceLogicalNot', 'reduceLogicalAnd', 'reduceLogicalOr'],
		'logicalOr' => [],
		'logicalAnd' => ['reduceLogicalOr'],
		'term' => ['reducePreference', 'reduceLogicalNot', 'reduceLogicalAnd', 'reduceLogicalOr'],
	];

	/**
	 * @var int[]
	 */
	private static $tokenShortcuts = [
		'operatorNot' => Tokenizer::TOKEN_LOGICAL_NOT | Tokenizer::TOKEN_LOGICAL_NOT_2,
		'operatorPreference' => Tokenizer::TOKEN_MANDATORY | Tokenizer::TOKEN_PROHIBITED,
		'operatorPrefix' => Tokenizer::TOKEN_MANDATORY | Tokenizer::TOKEN_PROHIBITED | Tokenizer::TOKEN_LOGICAL_NOT_2,
		'operatorUnary' => Tokenizer::TOKEN_MANDATORY | Tokenizer::TOKEN_PROHIBITED | Tokenizer::TOKEN_LOGICAL_NOT
		| Tokenizer::TOKEN_LOGICAL_NOT_2,
		'operatorBinary' => Tokenizer::TOKEN_LOGICAL_AND | Tokenizer::TOKEN_LOGICAL_OR,
		'operator' => Tokenizer::TOKEN_LOGICAL_AND | Tokenizer::TOKEN_LOGICAL_OR | Tokenizer::TOKEN_MANDATORY
		| Tokenizer::TOKEN_PROHIBITED | Tokenizer::TOKEN_LOGICAL_NOT | Tokenizer::TOKEN_LOGICAL_NOT_2,
		'groupDelimiter' => Tokenizer::TOKEN_GROUP_BEGIN | Tokenizer::TOKEN_GROUP_END,
		'binaryOperatorAndWhitespace' => Tokenizer::TOKEN_LOGICAL_AND | Tokenizer::TOKEN_LOGICAL_OR
		| Tokenizer::TOKEN_WHITESPACE,
	];

	/**
	 * @var string[]
	 */
	private static $shifts = [
		Tokenizer::TOKEN_WHITESPACE => 'shiftWhitespace',
		Tokenizer::TOKEN_TERM => 'shiftTerm',
		Tokenizer::TOKEN_GROUP_BEGIN => 'shiftGroupBegin',
		Tokenizer::TOKEN_GROUP_END => 'shiftGroupEnd',
		Tokenizer::TOKEN_LOGICAL_AND => 'shiftBinaryOperator',
		Tokenizer::TOKEN_LOGICAL_OR => 'shiftBinaryOperator',
		Tokenizer::TOKEN_LOGICAL_NOT => 'shiftLogicalNot',
		Tokenizer::TOKEN_LOGICAL_NOT_2 => 'shiftLogicalNot2',
		Tokenizer::TOKEN_MANDATORY => 'shiftPreference',
		Tokenizer::TOKEN_PROHIBITED => 'shiftPreference',
		Tokenizer::TOKEN_BAILOUT => 'shiftBailout',
	];

	/**
	 * @var string[]
	 */
	private static $nodeToReductionGroup = [
		Group::class => 'group',
		LogicalAnd::class => 'logicalAnd',
		LogicalOr::class => 'logicalOr',
		LogicalNot::class => 'unaryOperator',
		Mandatory::class => 'unaryOperator',
		Prohibited::class => 'unaryOperator',
		Term::class => 'term',
	];

	/**
	 * Input tokens.
	 *
	 * @var Token[]
	 */
	private $tokens = [];

	/**
	 * An array of applied corrections.
	 *
	 * @var Correction[]
	 */
	private $corrections = [];

	/**
	 * Query stack.
	 *
	 * @var SplStack
	 */
	private $stack;


	public function parse(TokenSequence $tokenSequence): SyntaxTree
	{
		$this->init($tokenSequence->getTokens());

		while ($this->tokens !== []) {
			$node = $this->shift();

			if ($node instanceof AbstractNode) {
				$this->reduce($node);
			}
		}

		$this->reduceQuery();

		return new SyntaxTree($this->stack->top(), $tokenSequence, $this->corrections);
	}


	public function ignoreLogicalNotOperatorsPrecedingPreferenceOperator(): void
	{
		$precedingOperators = $this->ignorePrecedingOperators(self::$tokenShortcuts['operatorNot']);

		if ($precedingOperators !== []) {
			$this->addCorrection(
				self::CORRECTION_LOGICAL_NOT_OPERATORS_PRECEDING_PREFERENCE_IGNORED,
				...$precedingOperators
			);
		}
	}


	private function shiftWhitespace(): void
	{
		if ($this->isTopStackToken(self::$tokenShortcuts['operatorPrefix'])) {
			$this->addCorrection(self::CORRECTION_UNARY_OPERATOR_MISSING_OPERAND_IGNORED, $this->stack->pop());
		}
	}


	private function shiftPreference(Token $token): void
	{
		$this->shiftAdjacentUnaryOperator($token, self::$tokenShortcuts['operator']);
	}


	private function shiftAdjacentUnaryOperator(Token $token, ?int $tokenMask): void
	{
		if ($this->isToken(reset($this->tokens), $tokenMask)) {
			$this->addCorrection(self::CORRECTION_ADJACENT_UNARY_OPERATOR_PRECEDING_OPERATOR_IGNORED, $token);

			return;
		}

		$this->stack->push($token);
	}


	private function shiftLogicalNot(Token $token): void
	{
		$this->stack->push($token);
	}


	private function shiftLogicalNot2(Token $token): void
	{
		$tokenMask = self::$tokenShortcuts['operator'] & ~Tokenizer::TOKEN_LOGICAL_NOT_2;

		$this->shiftAdjacentUnaryOperator($token, $tokenMask);
	}


	private function shiftBinaryOperator(Token $token): void
	{
		if ($this->stack->isEmpty() || $this->isTopStackToken(Tokenizer::TOKEN_GROUP_BEGIN)) {
			$this->addCorrection(self::CORRECTION_BINARY_OPERATOR_MISSING_LEFT_OPERAND_IGNORED, $token);

			return;
		}

		if ($this->isTopStackToken(self::$tokenShortcuts['operator'])) {
			$this->ignoreBinaryOperatorFollowingOperator($token);

			return;
		}

		$this->stack->push($token);
	}


	private function shiftTerm(Token $token): Term
	{
		return new Term($token);
	}


	private function shiftGroupBegin(Token $token): void
	{
		$this->stack->push($token);
	}


	private function shiftGroupEnd(Token $token): Group
	{
		$this->stack->push($token);

		return new Group;
	}


	private function shiftBailout(Token $token): void
	{
		$this->addCorrection(self::CORRECTION_BAILOUT_TOKEN_IGNORED, $token);
	}


	private function reducePreference(AbstractNode $node): AbstractNode
	{
		if (! $this->isTopStackToken(self::$tokenShortcuts['operatorPreference'])) {
			return $node;
		}

		$token = $this->stack->pop();

		if ($this->isToken($token, Tokenizer::TOKEN_MANDATORY)) {
			return new Mandatory($node, $token);
		}

		return new Prohibited($node, $token);
	}


	private function reduceLogicalNot(AbstractNode $node): AbstractNode
	{
		if (! $this->isTopStackToken(self::$tokenShortcuts['operatorNot'])) {
			return $node;
		}

		if ($node instanceof Mandatory || $node instanceof Prohibited) {
			$this->ignoreLogicalNotOperatorsPrecedingPreferenceOperator();

			return $node;
		}

		return new LogicalNot($node, $this->stack->pop());
	}


	private function reduceLogicalAnd(AbstractNode $node): AbstractNode
	{
		if ($this->stack->count() <= 1 || ! $this->isTopStackToken(Tokenizer::TOKEN_LOGICAL_AND)) {
			return $node;
		}

		$token = $this->stack->pop();
		$leftOperand = $this->stack->pop();

		return new LogicalAnd($leftOperand, $node, $token);
	}


	/**
	 * Reduce logical OR.
	 *
	 * @param bool $inGroup Reduce inside a group
	 * @return LogicalOr|AbstractNode|null
	 */
	private function reduceLogicalOr(AbstractNode $node, bool $inGroup = false)
	{
		if ($this->stack->count() <= 1 || ! $this->isTopStackToken(Tokenizer::TOKEN_LOGICAL_OR)) {
			return $node;
		}

		// If inside a group don't look for following logical AND
		if (! $inGroup) {
			$this->popWhitespace();
			// If the next token is logical AND, put the node on stack
			// as that has precedence over logical OR
			if ($this->isToken(reset($this->tokens), Tokenizer::TOKEN_LOGICAL_AND)) {
				$this->stack->push($node);

				return null;
			}
		}

		$token = $this->stack->pop();
		$leftOperand = $this->stack->pop();

		return new LogicalOr($leftOperand, $node, $token);
	}


	private function reduceGroup(Group $group): ?Group
	{
		$rightDelimiter = $this->stack->pop();

		// Pop dangling tokens
		$this->popTokens(~Tokenizer::TOKEN_GROUP_BEGIN);

		if ($this->isTopStackToken(Tokenizer::TOKEN_GROUP_BEGIN)) {
			$leftDelimiter = $this->stack->pop();
			$this->ignoreEmptyGroup($leftDelimiter, $rightDelimiter);
			$this->reduceRemainingLogicalOr(true);

			return null;
		}

		$this->reduceRemainingLogicalOr(true);

		$group->setNodes($this->collectTopStackNodes());
		$group->setTokenLeft($this->stack->pop());
		$group->setTokenRight($rightDelimiter);

		return $group;
	}


	/**
	 * @return mixed
	 */
	private function shift()
	{
		$token = array_shift($this->tokens);
		$shift = self::$shifts[$token->getType()];

		return $this->{$shift}($token);
	}


	private function reduce(AbstractNode $node): void
	{
		$previousNode = null;
		$reductionIndex = null;

		while ($node instanceof AbstractNode) {
			// Reset reduction index on first iteration or on Node change
			if ($node !== $previousNode) {
				$reductionIndex = 0;
			}

			// If there are no reductions to try, put the Node on the stack
			// and continue shifting
			$reduction = $this->getReduction($node, $reductionIndex);
			if ($reduction === null) {
				$this->stack->push($node);
				break;
			}

			$previousNode = $node;
			$node = $this->{$reduction}($node);
			++$reductionIndex;
		}
	}


	private function ignoreBinaryOperatorFollowingOperator(Token $token): void
	{
		$precedingOperators = $this->ignorePrecedingOperators(self::$tokenShortcuts['operator']);
		$followingOperators = $this->ignoreFollowingOperators();

		$this->addCorrection(
			self::CORRECTION_BINARY_OPERATOR_FOLLOWING_OPERATOR_IGNORED,
			...array_merge($precedingOperators, [$token], $followingOperators)
		);
	}


	/**
	 * Collect all Nodes from the top of the stack.
	 *
	 * @return AbstractNode[]
	 */
	private function collectTopStackNodes()
	{
		$nodes = [];

		while (! $this->stack->isEmpty() && $this->stack->top() instanceof AbstractNode) {
			array_unshift($nodes, $this->stack->pop());
		}

		return $nodes;
	}


	private function ignoreEmptyGroup(Token $leftDelimiter, Token $rightDelimiter): void
	{
		$precedingOperators = $this->ignorePrecedingOperators(self::$tokenShortcuts['operator']);
		$followingOperators = $this->ignoreFollowingOperators();

		$this->addCorrection(
			self::CORRECTION_EMPTY_GROUP_IGNORED,
			...array_merge($precedingOperators, [$leftDelimiter, $rightDelimiter], $followingOperators)
		);
	}


	/**
	 * Initialize the parser with given array of $tokens.
	 *
	 * @param Token[] $tokens
	 */
	private function init(array $tokens): void
	{
		$this->corrections = [];
		$this->tokens = $tokens;
		$this->cleanupGroupDelimiters($this->tokens);
		$this->stack = new SplStack();
	}


	private function getReduction(AbstractNode $node, int $reductionIndex): ?string
	{
		$reductionGroup = self::$nodeToReductionGroup[get_class($node)];

		if (isset(self::$reductionGroups[$reductionGroup][$reductionIndex])) {
			return self::$reductionGroups[$reductionGroup][$reductionIndex];
		}

		return null;
	}


	private function reduceQuery(): void
	{
		$this->popTokens();
		$this->reduceRemainingLogicalOr();
		$nodes = [];

		while (! $this->stack->isEmpty()) {
			array_unshift($nodes, $this->stack->pop());
		}

		$this->stack->push(new Query($nodes));
	}


	/**
	 * Check if the given $token is an instance of Token.
	 *
	 * Optionally also checks given Token $typeMask.
	 *
	 * @param mixed $token
	 * @param int $typeMask
	 *
	 * @return bool
	 */
	private function isToken($token, $typeMask = null)
	{
		if (! $token instanceof Token) {
			return false;
		}

		if ($typeMask === null || (bool) ($token->getType() & $typeMask)) {
			return true;
		}

		return false;
	}


	private function isTopStackToken(?int $type = null): bool
	{
		return ! $this->stack->isEmpty() && $this->isToken($this->stack->top(), $type);
	}


	/**
	 * Remove whitespace Tokens from the beginning of the token array.
	 */
	private function popWhitespace(): void
	{
		while ($this->isToken(reset($this->tokens), Tokenizer::TOKEN_WHITESPACE)) {
			array_shift($this->tokens);
		}
	}


	/**
	 * Remove all Tokens from the top of the query stack and log Corrections as necessary.
	 *
	 * Optionally also checks that Token matches given $typeMask.
	 *
	 * @param int $typeMask
	 */
	private function popTokens($typeMask = null): void
	{
		while ($this->isTopStackToken($typeMask)) {
			/** @var Token $token */
			$token = $this->stack->pop();
			if ((bool) ($token->getType() & self::$tokenShortcuts['operatorUnary'])) {
				$this->addCorrection(self::CORRECTION_UNARY_OPERATOR_MISSING_OPERAND_IGNORED, $token);
			} else {
				$this->addCorrection(self::CORRECTION_BINARY_OPERATOR_MISSING_RIGHT_OPERAND_IGNORED, $token);
			}
		}
	}


	private function ignorePrecedingOperators(?int $type): array
	{
		$tokens = [];
		while ($this->isTopStackToken($type)) {
			array_unshift($tokens, $this->stack->pop());
		}

		return $tokens;
	}


	private function ignoreFollowingOperators(): array
	{
		$tokenMask = self::$tokenShortcuts['binaryOperatorAndWhitespace'];
		$tokens = [];
		while ($this->isToken(reset($this->tokens), $tokenMask)) {
			$token = array_shift($this->tokens);
			if ((bool) ($token->getType() & self::$tokenShortcuts['operatorBinary'])) {
				$tokens[] = $token;
			}
		}

		return $tokens;
	}


	/**
	 * Reduce logical OR possibly remaining after reaching end of group or query.
	 *
	 * @param bool $inGroup Reduce inside a group
	 */
	private function reduceRemainingLogicalOr($inGroup = false): void
	{
		if (! $this->stack->isEmpty() && ! $this->isTopStackToken()) {
			$node = $this->reduceLogicalOr($this->stack->pop(), $inGroup);
			$this->stack->push($node);
		}
	}


	/**
	 * Clean up group delimiter tokens, removing unmatched left and right delimiter.
	 *
	 * Closest group delimiters will be matched first, unmatched remainder is removed.
	 *
	 * @param Token[] $tokens
	 */
	private function cleanupGroupDelimiters(array &$tokens): void
	{
		$indexes = $this->getUnmatchedGroupDelimiterIndexes($tokens);

		while (count($indexes) > 0) {
			$lastIndex = array_pop($indexes);
			$token = $tokens[$lastIndex];
			unset($tokens[$lastIndex]);

			if ($token->getType() === Tokenizer::TOKEN_GROUP_BEGIN) {
				$this->addCorrection(self::CORRECTION_UNMATCHED_GROUP_LEFT_DELIMITER_IGNORED, $token);
			} else {
				$this->addCorrection(self::CORRECTION_UNMATCHED_GROUP_RIGHT_DELIMITER_IGNORED, $token);
			}
		}
	}


	private function getUnmatchedGroupDelimiterIndexes(array &$tokens): array
	{
		$trackLeft = [];
		$trackRight = [];

		foreach ($tokens as $index => $token) {
			if (! $this->isToken($token, self::$tokenShortcuts['groupDelimiter'])) {
				continue;
			}

			if ($this->isToken($token, Tokenizer::TOKEN_GROUP_BEGIN)) {
				$trackLeft[] = $index;
				continue;
			}

			if (count($trackLeft) === 0) {
				$trackRight[] = $index;
			} else {
				array_pop($trackLeft);
			}
		}

		return array_merge($trackLeft, $trackRight);
	}


	/**
	 * @param mixed $type
	 */
	private function addCorrection($type, Token ...$tokens): void
	{
		$this->corrections[] = new Correction($type, ...$tokens);
	}

}
