<?php

namespace Tests\Unit;

use App\Enums\QuizDifficulty;
use PHPUnit\Framework\TestCase;

class QuizDifficultyTest extends TestCase
{
    public function test_each_difficulty_has_the_expected_points_value(): void
    {
        $this->assertSame(10, QuizDifficulty::Easy->pointsPerAnswer());
        $this->assertSame(20, QuizDifficulty::Medium->pointsPerAnswer());
        $this->assertSame(30, QuizDifficulty::Hard->pointsPerAnswer());
    }
}
