<?php
namespace App\Services\AI\Plugins\Contracts;

interface RetrieverPlugin {
  public function supports(string $question, array $ctx): bool;
  public function handle(string $question, array $ctx): array; // schema
}
