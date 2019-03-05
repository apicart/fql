<?php declare(strict_types = 1);

namespace Apicart\FQL\Tokenizer;

use Apicart\FQL\Token\Token\GroupBegin;
use Apicart\FQL\Value\Token;
use RuntimeException;

abstract class AbstractTokenExtractor
{

	/**
	 * Return the token at the given $position of the $string.
	 *
	 * @throws RuntimeException On PCRE regex error
	 */
	final public function extract(string $string, int $position): Token
	{
		$byteOffset = $this->getByteOffset($string, $position);
		foreach ($this->getExpressionTypeMap() as $expression => $type) {
			$success = preg_match($expression, $string, $matches, 0, $byteOffset);
			if ($success === false) {
				throw new RuntimeException('PCRE regex error code: ' . preg_last_error());
			}
			if ($success === 0) {
				continue;
			}
			return $this->createToken($type, $position, $matches);
		}
		return new Token(Tokenizer::TOKEN_BAILOUT, mb_substr($string, $position, 1), $position);
	}


	/**
	 * Return a map of regular expressions to token types.
	 *
	 * The returned map must be an array where key is a regular expression
	 * and value is a corresponding token type. Regular expression must define
	 * named capturing group 'lexeme' that identifies part of the input string
	 * recognized as token.
	 */
	abstract protected function getExpressionTypeMap(): array;


	/**
	 * Create a term type token by the given parameters.
	 *
	 * @throw RuntimeException If token could not be created from the given $matches data
	 */
	abstract protected function createTermToken(int $position, array $data): Token;


	/**
	 * Create an instance of Group token by the given parameters.
	 */
	protected function createGroupBeginToken(int $position, array $data): GroupBegin
	{
		return new GroupBegin($data['lexeme'], $position, $data['delimiter'], $data['domain']);
	}


	private function createToken(int $type, int $position, array $data): Token
	{
		if ($type === Tokenizer::TOKEN_GROUP_BEGIN) {
			return $this->createGroupBeginToken($position, $data);
		}
		if ($type === Tokenizer::TOKEN_TERM) {
			return $this->createTermToken($position, $data);
		}
		return new Token($type, $data['lexeme'], $position);
	}


	/**
	 * Return the offset of the given $position in the input $string, in bytes.
	 *
	 * Offset in bytes is needed for preg_match $offset parameter.
	 */
	private function getByteOffset(string $string, int $position): int
	{
		return strlen(mb_substr($string, 0, $position));
	}

}
