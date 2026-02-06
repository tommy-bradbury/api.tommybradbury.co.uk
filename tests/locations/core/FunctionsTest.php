<?php

namespace Locations\Core;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../src/locations/core/functions.php';

class FunctionsTest extends TestCase
{
    public function testCreateLocation(): void
    {
        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->with([
                ':user_id' => 1,
                ':name' => 'Test Location',
                ':whatthreewords' => 'test.what.three',
                ':latitude' => 1.23,
                ':longitude' => 4.56
            ]);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO locations (user_id, name, whatthreewords, latitude, longitude) VALUES (:user_id, :name, :whatthreewords, :latitude, :longitude)')
            ->willReturn($pdoStatementMock);
        $pdoMock->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('123');

        $locationId = createLocation($pdoMock, 1, 'Test Location', 'test.what.three', 1.23, 4.56);

        $this->assertSame(123, $locationId);
    }

    public function testUpdateLocation(): void
    {
        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->with([
                ':id' => 123,
                ':user_id' => 1,
                ':name' => 'Updated Location',
                ':whatthreewords' => 'updated.what.three',
                ':latitude' => 7.89,
                ':longitude' => 10.11
            ]);
        $pdoStatementMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->with('UPDATE locations SET name = :name, whatthreewords = :whatthreewords, latitude = :latitude, longitude = :longitude WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL')
            ->willReturn($pdoStatementMock);

        $result = updateLocation($pdoMock, 123, 1, 'Updated Location', 'updated.what.three', 7.89, 10.11);

        $this->assertTrue($result);
    }

    public function testDeleteLocation(): void
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
            ->with('UPDATE locations SET deleted_at = NOW() WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL')
            ->willReturn($pdoStatementMock);

        $result = deleteLocation($pdoMock, 123, 1);

        $this->assertTrue($result);
    }
}