<?php declare(strict_types = 1);

namespace Apicart\FQL\Contract\Tokenizer;

use Apicart\FQL\Value\TokenSequence;

interface TokenizerInterface
{

	public function tokenize(string $text): TokenSequence;

}
