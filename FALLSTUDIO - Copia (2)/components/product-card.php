<?php
/**
 * Componente Product Card
 * @var array $product {
 *     @var string $id
 *     @var string $name
 *     @var string $image
 *     @var string $hoverImage (opzionale)
 *     @var string $description
 *     @var bool $isNew (opzionale)
 * }
 */
$id = $product['id'] ?? '';
$name = $product['name'] ?? '';
$image = $product['image'] ?? '';
$hoverImage = $product['hoverImage'] ?? '';
$description = $product['description'] ?? '';
$isNew = $product['isNew'] ?? false;
?>
<div class="product-card" data-product="<?php echo htmlspecialchars($id); ?>">
    <div class="product-image">
        <img src="<?php echo htmlspecialchars($image); ?>" 
             alt="<?php echo htmlspecialchars($name); ?>" 
             loading="lazy" 
             <?php echo $hoverImage ? 'data-hover-src="' . htmlspecialchars($hoverImage) . '"' : ''; ?>>
        <?php if ($isNew): ?>
            <span class="product-badge new">NUOVO</span>
        <?php
endif; ?>
        <button class="quick-view" aria-label="Anteprima rapida">
            <i class="far fa-eye"></i>
        </button>
    </div>
    <div class="product-info">
        <h3><?php echo htmlspecialchars($name); ?></h3>
        <p class="product-description"><?php echo htmlspecialchars($description); ?></p>
    </div>
</div>
