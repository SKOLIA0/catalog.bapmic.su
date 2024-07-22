<?php
require 'config.php';
require 'Database.php';
require 'Product.php';
require 'Catalog.php';
require 'BapmicData.php';
require 'Photo.php';

$config = require 'config.php';
$db = new Database($config);
$productModel = new Product($db);
$catalogModel = new Catalog($db);
$bapmicDataModel = new BapmicData($db);
$photoModel = new Photo($db);

$products = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $articles = explode(',', $_POST['articles']);
    $articles = array_map('trim', $articles);
    $products = $productModel->getProducts($articles);
}
?>

<?php include 'includes/header.php'; ?>

<?php if (!isset($products) || $products->num_rows == 0): ?>
    <div class="video-container">
        <video id="background-video" autoplay loop muted>
            <source src="http://catalog.bapmic.su/video/9f4e92c.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
<?php endif; ?>

<div class="container">
    <?php include 'includes/search_form.php'; ?>

    <?php if (isset($products)): ?>
        <?php if ($products->num_rows > 0): ?>
            <?php while ($product = $products->fetch_assoc()): ?>
                <?php include 'includes/product_card.php'; ?>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-results-message">Ничего не найдено.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/scripts.php'; ?>

<?php
$db->close();
?>
