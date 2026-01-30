<?php

/**
 * Create a new location
 *
 * @param PDO $pdo
 * @param int $userId
 * @param string|null $name
 * @param string|null $whatthreewords
 * @param float|null $latitude
 * @param float|null $longitude
 * @return int Location ID
 */
function createLocation(PDO $pdo, int $userId, ?string $name, ?string $whatthreewords, ?float $latitude, ?float $longitude): int
{
    $stmt = $pdo->prepare('INSERT INTO locations (user_id, name, whatthreewords, latitude, longitude) VALUES (:user_id, :name, :whatthreewords, :latitude, :longitude)');
    $stmt->execute([
        ':user_id' => $userId,
        ':name' => $name,
        ':whatthreewords' => $whatthreewords,
        ':latitude' => $latitude,
        ':longitude' => $longitude
    ]);
    return (int)$pdo->lastInsertId();
}

/**
 * Update an existing location
 *
 * @param PDO $pdo
 * @param int $locationId
 * @param int $userId
 * @param string|null $name
 * @param string|null $whatthreewords
 * @param float|null $latitude
 * @param float|null $longitude
 * @return bool
 */
function updateLocation(PDO $pdo, int $locationId, int $userId, ?string $name, ?string $whatthreewords, ?float $latitude, ?float $longitude): bool
{
    $stmt = $pdo->prepare('UPDATE locations SET name = :name, whatthreewords = :whatthreewords, latitude = :latitude, longitude = :longitude WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $stmt->execute([
        ':id' => $locationId,
        ':user_id' => $userId,
        ':name' => $name,
        ':whatthreewords' => $whatthreewords,
        ':latitude' => $latitude,
        ':longitude' => $longitude
    ]);
    return $stmt->rowCount() > 0;
}

/**
 * Soft delete a location
 *
 * @param PDO $pdo
 * @param int $locationId
 * @param int $userId
 * @return bool
 */
function deleteLocation(PDO $pdo, int $locationId, int $userId): bool
{
    $stmt = $pdo->prepare('UPDATE locations SET deleted_at = NOW() WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $stmt->execute([':id' => $locationId, ':user_id' => $userId]);
    return $stmt->rowCount() > 0;
}
