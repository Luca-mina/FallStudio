<?php
$pageTitle = 'Fall Studio | Streetwear & Abbigliamento';
include 'header.php';
?>

    <!-- Intro Banner -->
    <section class="intro-banner" aria-label="Intro">
        <div class="intro-content container">
            <h1>DON'T FALL</h1>
        </div>
    </section>

    <!-- Hero Banner rimosso -->

    <!-- Categorie Prodotti -->
    <!--<section class="categories">-
        <div class="container">
            <h2 class="section-title">Esplora le Categorie</h2>
            <div class="categories-grid">-->
               <!-- <?php /*  renderCategoryCard(['title' => 'Hoodies', 'image' => 'https://lh3.googleusercontent.com/d/1YJaXnd4gEBHIKA70B8cDcloT0YP4U87X', 'link' => 'collezione.php']);  renderCategoryCard(['title' => 'T-Shirt', 'image' => 'tee.png', 'link' => 'collezione.php']);  renderCategoryCard(['title' => 'Pantaloni', 'image' => 'https://lh3.googleusercontent.com/d/1UAOXAC-O3JoPv2C7C-Yjo5gawRCXheNZ', 'link' => 'collezione.php']);  renderCategoryCard(['title' => 'Accessori', 'image' => 'https://lh3.googleusercontent.com/d/1YCDUEXSKQOIsRaFHrAAs79CG9ugwLuH0', 'link' => 'accessori.php']);  */?>-->
            </div>
        </div>
    </section>

    <!-- Prodotti in Evidenza -->
    <section id="nuovi-arrivi" class="featured-products">
        <div class="container">
            <div class="section-header">
                <h2>Nuovi Arrivi</h2>
                <a href="collezione.php" class="view-all">Vedi tutti <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="products-grid">
                <?php
renderProductCard([
    'id' => '3',
    'name' => 'T-Shirt Fall001',
    'description' => ' ',
    'image' => 'immgini/TeeSfondoBianco.jpeg'
]);

renderProductCard([
    'id' => '1',
    'name' => 'Hoodie Brick "Don\'t Fall"',
    'description' => ' ',
    //'image' => 'FelpaRossaDalila.JPG',
    'image' => 'immgini/FelpaRossaFronte.png',
    //'hoverImage' => 'https://lh3.googleusercontent.com/d/1YCDUEXSKQOIsRaFHrAAs79CG9ugwLuH0',
    'isNew' => true
]);
renderProductCard([
    'id' => '4',
    'name' => 'Pants Brick "Don\'t Fall"',
    'description' => ' ',
    //'image' => 'https://lh3.googleusercontent.com/d/1YCDUEXSKQOIsRaFHrAAs79CG9ugwLuH0'
    'image' => 'immgini/PantaloniRossoFronte.png'
    //'hoverImage' => 'FelpaRossaDalila.JPG'
]);
renderProductCard([
    'id' => '2',
    'name' => 'Hoodie Mezzanotte "Don\'t Fall"',
    'description' => ' ',
    //'image' => 'PantaloniBluDalila.JPG',
    'image' => 'immgini/FelpaBluFronte.png',
    'isNew' => true
]);


renderProductCard([
    'id' => '5',
    'name' => 'Pants Mezzanotte "Don\'t Fall"',
    'description' => '',
    //'image' => 'https://lh3.googleusercontent.com/d/1UAOXAC-O3JoPv2C7C-Yjo5gawRCXheNZ'
    'image' => 'immgini/PantaloniBluFronte.png'
    //'hoverImage' => 'PantaloniBluDalila.JPG'
]);


renderProductCard([
    'id' => '6',
    'name' => 'Bracelet Chain Fall Studio',
    'description' => '',
    'image' => 'immgini\braccialetto5.jpeg',
    'isNew' => true
]);

renderProductCard([
    'id' => '7',
    'name' => 'Neckless Fall Studio',
    'description' => '',
    'image' => 'immgini\neckless1.jpeg',
    'isNew' => true
]);
?>
            </div>
        </div>
    </section>


<?php
include 'footer.php';
?>
