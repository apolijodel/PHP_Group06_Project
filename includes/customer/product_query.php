<?php
/**
 * One product query, shared by every place that lists products: the Shop
 * section on the home page, the full Shop page, and the AJAX endpoint the
 * category chips call.
 *
 * Keeping this in one function is what stops the section and the page from
 * drifting apart — and it means the AJAX path runs exactly the same SQL,
 * with the same prepared statements, as the no-JavaScript path.
 */
require_once __DIR__ . '/../functions.php';

/** Sort options, as [label, ORDER BY fragment]. Keys are the only values accepted. */
function product_sorts(): array
{
    return [
        'newest' => ['Newest first', 'p.created_at DESC'],
        'price_asc' => ['Price: low to high', 'p.price ASC'],
        'price_desc' => ['Price: high to low', 'p.price DESC'],
        'name_asc' => ['Name: A to Z', 'p.name ASC'],
    ];
}

/**
 * Normalise raw request input into a safe filter set.
 * Everything is cast or whitelisted here, so callers never pass raw $_GET on.
 */
function product_filters(array $input): array
{
    $sorts = product_sorts();
    $sort = isset($input['sort']) && isset($sorts[$input['sort']]) ? $input['sort'] : 'newest';

    return [
        'q' => trim((string)($input['q'] ?? '')),
        'category' => isset($input['category']) && $input['category'] !== ''
            ? (int)$input['category']
            : null,
        'customizable' => ($input['customizable'] ?? '') === '1',
        'in_stock' => ($input['in_stock'] ?? '') === '1',
        'min_price' => is_numeric($input['min_price'] ?? null) ? (float)$input['min_price'] : null,
        'max_price' => is_numeric($input['max_price'] ?? null) ? (float)$input['max_price'] : null,
        'sort' => $sort,
    ];
}

/**
 * Products matching $filters. Sold-out items always sort last, whichever
 * sort is chosen, so the first thing a customer sees is something buyable.
 *
 * Note the distinct placeholder names on the search branch: under
 * PDO::ATTR_EMULATE_PREPARES => false a named parameter may be bound only
 * once, so reusing a single :q across three OR arms is a fatal error.
 */
function fetch_products(array $filters, ?int $limit = null): array
{
    $sorts = product_sorts();
    $where = [];
    $params = [];

    if ($filters['q'] !== '') {
        $where[] = '(p.name LIKE :q_name OR p.description LIKE :q_desc OR c.name LIKE :q_cat)';
        $like = '%' . $filters['q'] . '%';
        $params['q_name'] = $like;
        $params['q_desc'] = $like;
        $params['q_cat'] = $like;
    }
    if ($filters['category']) {
        $where[] = 'p.category_id = :category';
        $params['category'] = $filters['category'];
    }
    if ($filters['customizable']) {
        $where[] = 'p.is_customizable = 1';
    }
    if ($filters['in_stock']) {
        $where[] = 'p.stock_quantity > 0';
    }
    if ($filters['min_price'] !== null) {
        $where[] = 'p.price >= :min_price';
        $params['min_price'] = $filters['min_price'];
    }
    if ($filters['max_price'] !== null) {
        $where[] = 'p.price <= :max_price';
        $params['max_price'] = $filters['max_price'];
    }

    $sql = 'SELECT p.*, c.name AS category_name
            FROM products p
            JOIN categories c ON c.category_id = p.category_id';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY (p.stock_quantity > 0) DESC, ' . $sorts[$filters['sort']][1];

    if ($limit !== null) {
        // Bound below as an integer, never interpolated from user input.
        $sql .= ' LIMIT :row_limit';
    }

    $stmt = db()->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    if ($limit !== null) {
        $stmt->bindValue(':row_limit', max(1, $limit), PDO::PARAM_INT);
    }
    $stmt->execute();

    return $stmt->fetchAll();
}

/** Categories with a live product count, for the filter chips. */
function categories_with_counts(): array
{
    return db()->query(
        'SELECT c.category_id, c.name, COUNT(p.product_id) AS product_count
         FROM categories c
         LEFT JOIN products p ON p.category_id = c.category_id
         GROUP BY c.category_id, c.name
         ORDER BY c.name'
    )->fetchAll();
}
