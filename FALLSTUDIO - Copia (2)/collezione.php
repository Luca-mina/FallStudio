<?php
$pageTitle = 'Fall Studio | Collezione';
include 'header.php';
?>

    <section class="featured-products">
        <div class="container">
            <div class="section-header">
                <h2>Collezione Completa</h2>
            </div>

            <div class="products-grid">
                <?php
renderProductCard([
    'id' => '1',
    'name' => 'Hoodie "Don\'t Fall"',
    'description' => 'HOODIE "Don\'t Fall"',
    'image' => 'https://lh3.googleusercontent.com/d/1YJaXnd4gEBHIKA70B8cDcloT0YP4U87X',
    'hoverImage' => 'https://lh3.googleusercontent.com/d/1YCDUEXSKQOIsRaFHrAAs79CG9ugwLuH0'
]);

renderProductCard([
    'id' => '2',
    'name' => 'T-Shirt Fall001',
    'description' => 'T-shirt in cotone pettinato, stampa serigrafica, taglio regular fit.',
    'image' => 'tee.png'
]);

renderProductCard([
    'id' => '3',
    'name' => 'Pants "Don\'t Fall"',
    'description' => 'Pants cargo in twill di cotone, multiple tasche, fit relaxed.',
    'image' => 'https://lh3.googleusercontent.com/d/1UAOXAC-O3JoPv2C7C-Yjo5gawRCXheNZ'
]);

renderProductCard([
    'id' => '4',
    'name' => 'Chain "Fall Studio"',
    'description' => 'Chain Fall Studio',
    'image' => 'immgini\braccialetto3.jpg'
]);
?>
            </div>
        </div>
    </section>

<?php
include 'footer.php';
?>
