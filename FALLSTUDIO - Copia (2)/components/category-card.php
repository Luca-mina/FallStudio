<?php
/**
 * Componente Category Card
 * @var array $category {
 *     @var string $title
 *     @var string $image
 *     @var string $link
 * }
 */
$title = $category['title'] ?? '';
$image = $category['image'] ?? '';
$link = $category['link'] ?? '#';
?>
<a href="<?php echo htmlspecialchars($link); ?>" class="category-card">
    <div class="category-image" style="background-image: url('<?php echo htmlspecialchars($image); ?>');"></div>
    <h3><?php echo htmlspecialchars($title); ?></h3>
</a>
