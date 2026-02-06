<?php

namespace Reviews\Core;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../src/reviews/core/functions.php';

class FunctionsTest extends TestCase
{
    public function testCreateReview(): void
    {
        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->with([
                ':location_id' => 1,
                ':user_id' => 1,
                ':cleanliness_score' => 5,
                ':niceness_score' => 5,
                ':ease_of_finding_score' => 5,
                ':toilet_paper_quality_score' => 5,
                ':privacy_score' => 5,
                ':is_disabled_accessible' => 1,
                ':is_customers_only' => 0,
                ':has_baby_changing' => 1,
                ':has_gender_neutral' => 0,
                ':requires_payment' => 1,
                ':comment' => 'Test comment'
            ]);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO reviews (location_id, user_id, cleanliness_score, niceness_score, ease_of_finding_score, toilet_paper_quality_score, privacy_score, is_disabled_accessible, is_customers_only, has_baby_changing, has_gender_neutral, requires_payment, comment) VALUES (:location_id, :user_id, :cleanliness_score, :niceness_score, :ease_of_finding_score, :toilet_paper_quality_score, :privacy_score, :is_disabled_accessible, :is_customers_only, :has_baby_changing, :has_gender_neutral, :requires_payment, :comment)')
            ->willReturn($pdoStatementMock);
        $pdoMock->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('123');

        $reviewId = createReview($pdoMock, 1, 1, 5, 5, 5, 5, 5, true, false, true, false, true, 'Test comment');

        $this->assertSame(123, $reviewId);
    }

    public function testUpdateReview(): void
    {
        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->with([
                ':id' => 123,
                ':user_id' => 1,
                ':cleanliness_score' => 1,
                ':niceness_score' => 1,
                ':ease_of_finding_score' => 1,
                ':toilet_paper_quality_score' => 1,
                ':privacy_score' => 1,
                ':is_disabled_accessible' => 0,
                ':is_customers_only' => 1,
                ':has_baby_changing' => 0,
                ':has_gender_neutral' => 1,
                ':requires_payment' => 0,
                ':comment' => 'Updated comment'
            ]);
        $pdoStatementMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->with('UPDATE reviews SET cleanliness_score = :cleanliness_score, niceness_score = :niceness_score, ease_of_finding_score = :ease_of_finding_score, toilet_paper_quality_score = :toilet_paper_quality_score, privacy_score = :privacy_score, is_disabled_accessible = :is_disabled_accessible, is_customers_only = :is_customers_only, has_baby_changing = :has_baby_changing, has_gender_neutral = :has_gender_neutral, requires_payment = :requires_payment, comment = :comment WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL')
            ->willReturn($pdoStatementMock);

        $result = updateReview($pdoMock, 123, 1, 1, 1, 1, 1, 1, false, true, false, true, false, 'Updated comment');

        $this->assertTrue($result);
    }

    public function testDeleteReview(): void
    {
        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->with([':id' => 123, ':user_id' => 1]);
        $pdoStatementMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->with('UPDATE reviews SET deleted_at = NOW() WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL')
            ->willReturn($pdoStatementMock);

        $result = deleteReview($pdoMock, 123, 1);

        $this->assertTrue($result);
    }
}