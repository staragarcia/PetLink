<?php

declare(strict_types=1);
require_once('reviews.class.php');

class Ad {
    private int $id;
    private int $serviceId;
    private int $freelancerId;
    private string $title;
    private string $description;
    private float $price;
    private string $pricePeriod;
    private array $animals;
    public array $mediaIds;
    private string $username;
    private string $name;
    private int $userId;
    private string $district;
    private int $photoId;
    private ?string $createdAt;
    private ?float $averageRating;
    private ?int $reviewCount;


    private function __construct(
        int $id,
        int $serviceId,
        int $freelancerId,
        string $title,
        string $description,
        float $price,
        string $pricePeriod,
        array $animals,
        array $mediaIds,
        string $name,
        string $username,
        int $userId,
        string $district,
        int $photoId,
        ?string $createdAt,
        ?float $averageRating = null,
        ?int $reviewCount = null
    ) {
        $this->id = $id;
        $this->serviceId = $serviceId;
        $this->freelancerId = $freelancerId;
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->pricePeriod = $pricePeriod;
        $this->animals = $animals;
        $this->mediaIds = $mediaIds;
        $this->name = $name;
        $this->username = $username;
        $this->userId = $userId;
        $this->district = $district;
        $this->photoId = $photoId;
        $this->createdAt = $createdAt;
        $this->averageRating = $averageRating;
        $this->reviewCount = $reviewCount;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getServiceId(): int {
        return $this->serviceId;
    }

    public function getFreelancerId(): int {
        return $this->freelancerId;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function getPricePeriod(): string {
        return $this->pricePeriod;
    }

    public function getAnimals(): array {
        return $this->animals;
    }

    public function getMediaIds(): array {
        return $this->mediaIds;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function getDistrict(): string {
        return $this->district;
    }

    public function getPhotoId(): int {
        return $this->photoId;
    }

    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public function getAverageRating(): ?float {
        return $this->averageRating;
    }

    public function getReviewCount(): ?int {
        return $this->reviewCount;
    }

    private static function getAnimalsForAd(PDO $db, int $adId): array {
        $stmt = $db->prepare('
            SELECT at.animal_name
            FROM Ad_animals aa
            JOIN Animal_types at ON aa.animal_id = at.animal_id
            WHERE aa.ad_id = ?
        ');
        $stmt->execute([$adId]);

        $animals = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $animals[] = $row['animal_name'];
        }

        return $animals;
    }

    public static function getById(PDO $db, int $id): ?Ad {
        $stmt = $db->prepare('
            SELECT
                Ads.ad_id AS id,
                Ads.service_id,
                Ads.freelancer_id,
                Ads.title,
                Ads.description,
                Ads.price,
                Ads.price_period,
                Ads.created_at,
                Users.name,
                Users.username,
                Users.user_id,
                Users.district,
                Users.photo_id
            FROM Ads
            JOIN Users ON Ads.freelancer_id = Users.user_id
            WHERE Ads.ad_id = ?
        ');
        $stmt->execute([$id]);
        $adData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($adData === false) {
            return null;
        }

        $mediaIds = [];
        try {
            $mediaStmt = $db->prepare('
                SELECT media_id 
                FROM Ad_media 
                WHERE ad_id = ?
            ');
            $mediaStmt->execute([$id]);
            $mediaIds = $mediaStmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error fetching media for ad {$id}: " . $e->getMessage());
        }

        $averageRating = Reviews::getAverageRatingForAd($db, (int)$adData['id']);
        $reviewCount = Reviews::getReviewCountForAd($db, (int)$adData['id']);

        return new Ad (
            (int)$adData['id'],
            (int)$adData['service_id'],
            (int)$adData['freelancer_id'],
            $adData['title'] ?? '',
            $adData['description'] ?? '',
            (float)$adData['price'],
            $adData['price_period'] ?? '',
            self::getAnimalsForAd($db, (int)$adData['id']),
            $mediaIds,
            $adData['name'] ?? '',
            $adData['username'] ?? '',
            (int)$adData['user_id'],
            $adData['district'] ?? '',
            (int)($adData['photo_id'] ?? 0),
            $adData['created_at'] ?? null,
            $averageRating,
            $reviewCount
        );
    }

    public static function getAnuncio(PDO $db, int $ad_id): ?Ad {
        $stmt = $db->prepare('
            SELECT
                Ads.ad_id AS ad_id,
                Ads.service_id,
                Ads.freelancer_id,
                Ads.title,
                Ads.description,
                Ads.price,
                Ads.price_period,
                Ads.created_at,
                Users.name,
                Users.username,
                Users.user_id,
                Users.district,
                Users.photo_id
            FROM Ads
            JOIN Users ON Ads.freelancer_id = Users.user_id
            WHERE Ads.ad_id = ?
        ');
        $stmt->execute([$ad_id]);
        $adData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$adData) {
            return null;
        }

        $mediaIds = [];
        $mediaQuery = '
            SELECT media_id
            FROM Ad_media
            WHERE ad_id = ?
            ORDER BY media_id
        ';
        $mediaStmt = $db->prepare($mediaQuery);
        $mediaStmt->execute([$ad_id]);
        $adMedia = $mediaStmt->fetchAll(PDO::FETCH_COLUMN);
        if ($adMedia !== false) {
            $mediaIds = $adMedia;
        }
        $averageRating = Reviews::getAverageRatingForAd($db, (int)$adData['id']);
        $reviewCount = Reviews::getReviewCountForAd($db, (int)$adData['id']);

        return new Ad(
            (int)$adData['ad_id'],
            (int)$adData['service_id'],
            (int)$adData['freelancer_id'],
            $adData['title'] ?? '',
            $adData['description'] ?? '',
            (float)$adData['price'],
            $adData['price_period'] ?? '',
            self::getAnimalsForAd($db, (int)$adData['ad_id']),
            $mediaIds,
            $adData['name'] ?? '',
            $adData['username'] ?? '',
            (int)$adData['user_id'],
            $adData['district'] ?? '',
            (int)($adData['photo_id'] ?? 0),
            $adData['created_at'] ?? null,
            $averageRating,
            $reviewCount
        );
    }

    public static function getAll(PDO $db, int $page = 1, int $limit = 16): array {
        $offset = ($page - 1) * $limit;
        $query = '
            SELECT
                Ads.ad_id AS id,
                Ads.title,
                Ads.description,
                Ads.price,
                Ads.price_period,
                Ads.service_id,
                Ads.freelancer_id,
                Ads.created_at,
                Users.user_id,
                Users.name,
                Users.username,
                Users.district,
                Users.photo_id
            FROM Ads
            JOIN Users ON Ads.freelancer_id = Users.user_id
            ORDER BY Ads.ad_id DESC
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $db->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $adsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($adsData)) {
            return [];
        }

        $adIds = array_column($adsData, 'id');

        $ratingsQuery = '
            SELECT
                ad_id,
                AVG(rating) AS average_rating,
                COUNT(rating) AS review_count
            FROM Reviews
            WHERE ad_id IN (' . implode(',', array_fill(0, count($adIds), '?')) . ')
            GROUP BY ad_id
        ';
        $ratingsStmt = $db->prepare($ratingsQuery);
        $ratingsStmt->execute($adIds);
        $adRatings = $ratingsStmt->fetchAll(PDO::FETCH_ASSOC);

        $ratingsByAdId = [];
        foreach ($adRatings as $rating) {
                $ratingsByAdId[(int)$rating['ad_id']] = [
                'average_rating' => (float)$rating['average_rating'],
                'review_count' => (int)$rating['review_count']
            ];
        }

        $mediaQuery = '
            SELECT
                am.ad_id,
                am.media_id
            FROM Ad_media am
            WHERE am.ad_id IN (' . implode(',', array_fill(0, count($adIds), '?')) . ')
            ORDER BY am.ad_id, am.media_id
        ';

        $mediaStmt = $db->prepare($mediaQuery);
        $mediaStmt->execute($adIds);
        $adMedia = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);

        $mediaByAdId = [];
        foreach ($adMedia as $media) {
            $adId = $media['ad_id'];
            $mediaByAdId[$adId][] = (int)$media['media_id'];
        }

        $ads = [];
        foreach ($adsData as $adData) {
            $currentAdId = (int)$adData['id'];
            $adAnimals = self::getAnimalsForAd($db,  $currentAdId);
            $averageRating = $ratingsByAdId[$currentAdId]['average_rating'] ?? null;
            $reviewCount = $ratingsByAdId[$currentAdId]['review_count'] ?? null;
            $ads[] = new Ad(
                (int)$adData['id'],
                (int)($adData['service_id'] ?? 0),
                (int)($adData['freelancer_id'] ?? 0),
                $adData['title'] ?? '',
                $adData['description'] ?? '',
                (float)$adData['price'],
                $adData['price_period'] ?? '',
                $adAnimals,
                $mediaByAdId[(int)$adData['id']] ?? [],
                $adData['name'] ?? '',
                $adData['username'] ?? '',
                (int)$adData['user_id'],
                $adData['district'] ?? '',
                (int)($adData['photo_id'] ?? 0),
                $adData['created_at'] ?? null,
                $averageRating,
                $reviewCount

            );
        }

        return $ads;
    }

    public static function getAllAdsReport(PDO $db): array{
        $query = '
            SELECT
                Ads.ad_id,
                Ads.title,
                Ads.freelancer_id,
                Ads.price,
                Ads.price_period
            FROM Ads
            ORDER BY Ads.created_at ASC
        ';

        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTotalCount(PDO $db): int {
        $query = 'SELECT COUNT(*) FROM Ads';
        $stmt = $db->prepare($query);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }


    public static function search(PDO $db, array $filters, int $page = 1, int $limit = 16): array {
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(Ads.title LIKE ? OR Ads.description LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['location'])) {
            $where[] = 'LOWER(Users.district) = ?';
            $params[] = strtolower($filters['location']);
        }
        if (!empty($filters['duracao'])) {
            $where[] = 'LOWER(Ads.price_period) = ?';
            $params[] = strtolower($filters['duracao']);
        }

        if (!empty($filters['animal'])) {
            $where[] = 'EXISTS (SELECT 1 FROM Ad_animals aa WHERE aa.ad_id = Ads.ad_id AND aa.animal_id = ?)';
            $params[] = (int)$filters['animal'];
        }

        if (!empty($filters['servico'])) {
            $where[] = 'EXISTS (SELECT 1 FROM Ad_services s WHERE s.ad_id = Ads.ad_id AND s.service_id = ?)';
            $params[] = (int)$filters['servico'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = 'Users.user_id = ?';
            $params[] = $filters['user_id'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $orderBy = 'Ads.ad_id DESC';
            if (!empty($filters['sort'])) {
            if ($filters['sort'] === 'preco_asc') $orderBy = 'Ads.price ASC, Ads.ad_id ASC';
            elseif ($filters['sort'] === 'preco_desc') $orderBy = 'Ads.price DESC, Ads.ad_id DESC';
        }

        $offset = ($page - 1) * $limit;
        $sql = "
            SELECT
                Ads.ad_id AS id,
                Ads.title,
                Ads.description,
                Ads.price,
                Ads.price_period,
                Ads.service_id,
                Ads.freelancer_id,
                Ads.created_at,
                Users.user_id,
                Users.name,
                Users.username,
                Users.district,
                Users.photo_id
                FROM Ads
                JOIN Users ON Ads.freelancer_id = Users.user_id
                $whereSql
                ORDER BY $orderBy
                LIMIT ? OFFSET ?
            ";

        $stmt = $db->prepare($sql);

        $paramIndex = 1;
        foreach ($params as $param) {
            $stmt->bindValue($paramIndex++, $param);
        }

        $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $adsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($adsData)) {
            return [];
        }
        $adIds = array_column($adsData, 'id');

        $ratingsQuery = '
            SELECT
                ad_id,
                AVG(rating) AS average_rating,
                COUNT(rating) AS review_count
            FROM Reviews
            WHERE ad_id IN (' . implode(',', array_fill(0, count($adIds), '?')) . ')
            GROUP BY ad_id
        ';
        $ratingsStmt = $db->prepare($ratingsQuery);
        $ratingsStmt->execute($adIds);
        $adRatings = $ratingsStmt->fetchAll(PDO::FETCH_ASSOC);

        $ratingsByAdId = [];

        foreach ($adRatings as $rating) {
            $ratingsByAdId[(int)$rating['ad_id']] = [
                'average_rating' => (float)$rating['average_rating'],
                'review_count' => (int)$rating['review_count']
            ];
        }


        $mediaQuery = '
            SELECT
                am.ad_id,
                am.media_id
            FROM Ad_media am
            WHERE am.ad_id IN (' . implode(',', array_fill(0, count($adIds), '?')) . ')
            ORDER BY am.ad_id, am.media_id
        ';
        $mediaStmt = $db->prepare($mediaQuery);
        $mediaStmt->execute($adIds);
        $adMedia = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);
        $mediaByAdId = [];

        foreach ($adMedia as $media) {
            $adId = $media['ad_id'];
            $mediaByAdId[$adId][] = (int)$media['media_id'];
        }

            $ads = [];
            foreach ($adsData as $adData) {
                $currentAdId = (int)$adData['id'];
                $adAnimals = self::getAnimalsForAd($db, $currentAdId);
                $averageRating = $ratingsByAdId[$currentAdId]['average_rating'] ?? null;
                $reviewCount = $ratingsByAdId[$currentAdId]['review_count'] ?? null;

                $ads[] = new Ad(
                    (int)$adData['id'],
                    (int)($adData['service_id'] ?? 0),
                    (int)($adData['freelancer_id'] ?? 0),
                    $adData['title'] ?? '',
                    $adData['description'] ?? '',
                    (float)$adData['price'],
                    $adData['price_period'] ?? '',
                    $adAnimals,
                    $mediaByAdId[(int)$adData['id']] ?? [],
                    $adData['name'] ?? '',
                    $adData['username'] ?? '',
                    (int)$adData['user_id'],
                    $adData['district'] ?? '',
                    (int)($adData['photo_id'] ?? 0),
                    $adData['created_at'] ?? null,
                    $averageRating,
                    $reviewCount
                );
            }

            return $ads;
        }

        public static function countSearch(PDO $db, array $filters): int {
    $where = [];
    $params = [];

    if (!empty($filters['search'])) {
        $where[] = '(Ads.title LIKE ? OR Ads.description LIKE ?)';
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }
    if (!empty($filters['location'])) {
        $where[] = 'LOWER(Users.district) = ?';
        $params[] = strtolower($filters['location']);
    }
    if (!empty($filters['duracao'])) {
        $where[] = 'LOWER(Ads.price_period) = ?';
        $params[] = strtolower($filters['duracao']);
    }
    if (!empty($filters['animal'])) {
        $where[] = 'EXISTS (SELECT 1 FROM Ad_animals aa WHERE aa.ad_id = Ads.ad_id AND aa.animal_id = ?)';
        $params[] = (int)$filters['animal'];
    }
    if (!empty($filters['servico'])) {
        $where[] = 'Ads.service_id = ?';
        $params[] = (int)$filters['servico'];
    }
    if (!empty($filters['user_id'])) {
        $where[] = 'Users.user_id = ?';
        $params[] = $filters['user_id'];
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "
        SELECT COUNT(*)
        FROM Ads
        JOIN Users ON Ads.freelancer_id = Users.user_id
        $whereSql
    ";

    $stmt = $db->prepare($sql);
    foreach ($params as $i => $param) {
        $stmt->bindValue($i + 1, $param);
    }
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}
}
