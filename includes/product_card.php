<?php 

$catalog = $catalogModel->getCatalog($product['article_from_supplier']);
$catalog_item = $catalog->fetch_assoc();

if ($catalog_item):
    $photo_path = 'photo/' . $catalog_item['article_from_supplier'] . '_';
    $default_photo = 'photo/bapmic_default.jpg'; // путь к изображению по умолчанию
?>
    <style>
        #background-video {
            display: none;
        }
    </style>
    <div class="product-card">
        <div class="product-top">
            <div class="photo-column">
                <div class="photo-list">
                    <?php 
                    $main_photo = $default_photo;
                    $photo_urls = [];
                    for ($i = 1; $i <= 25; $i++) {
                        $photo_file = $photo_path . str_pad($i, 2, '0', STR_PAD_LEFT) . '.jpg'; // 01, 02, 03...
                        if (file_exists($photo_file)) {
                            if ($main_photo === $default_photo) {
                                $main_photo = $photo_file;
                            }
                            $photo_urls[] = $photo_file;
                            echo "<img src='{$photo_file}' class='small-photo' alt='Product Photo'>";
                        }
                    }
                    // Если фотографии не найдены, используем фото по умолчанию
                    if (empty($photo_urls)) {
                        echo "<img src='{$default_photo}' class='small-photo' alt='Default Product Photo'>";
                    }
                    ?>
                </div>
            </div>
            <div class="main-photo">
                <img src="<?php echo $main_photo; ?>" class="product-photo" alt="Product Photo">
            </div>
            <div class="product-details">
                <h2><?php echo $catalog_item['article_from_supplier']; ?> - <?php echo $catalog_item['Name']; ?></h2>
                <table>
                    <tr>
                        <th>Производитель</th>
                        <td><?php echo $catalog_item['Manufacturer']; ?></td>
                    </tr>
                    <tr>
                        <th>Масса нетто (кг)</th>
                        <td><?php echo $catalog_item['Net_weight_kg_netto']; ?></td>
                    </tr>
                    <tr>
                        <th>Масса брутто (кг)</th>
                        <td><?php echo $catalog_item['Gross_weight_kg_brutto']; ?></td>
                    </tr>
                    <tr>
                        <th>Длина (см)</th>
                        <td><?php echo $catalog_item['Length_cm']; ?></td>
                    </tr>
                    <tr>
                        <th>Ширина (см)</th>
                        <td><?php echo $catalog_item['Width_cm']; ?></td>
                    </tr>
                    <tr>
                        <th>Высота (см)</th>
                        <td><?php echo $catalog_item['Height_cm']; ?></td>
                    </tr>
                </table>
                <?php if (isset($catalog_item['note'])): ?>
                    <div class="product-note">
                        <h3>Примечание:</h3>
                        <p><?php echo nl2br($catalog_item['note']); ?></p>
                    </div>
                <?php else: ?>
                    <div class="product-note">
                        <h3>Примечание:</h3>
                        <p></p>
                    </div>
                <?php endif; ?>
                <div class="button-container">
                    <button class="view-full-size" onclick="window.location.href='https://bapmic.pro/#4'">Узнать о наличии</button>
                </div>
            </div>
        </div>
        <div class="product-bottom">
            <div class="oe-section">
                <h3>OE:</h3>
                <div class="oe-column">
                    <p><?php echo str_replace('/', ' ', $catalog_item['OE']); ?></p>
                </div>
            </div>
            <div class="product-specs">
                <h3>Подходящие автомобили:</h3>
                <div class="scrollable-table">
                    <table>
                        <tr>
                            <th>Производитель</th>
                            <th>Модель</th>
                            <th>Тип привода</th>
                            <th>Год выпуска</th>
                            <th>Год окончания выпуска</th>
                        </tr>
                        <?php 
                        $bapmic_data = $bapmicDataModel->getBAPMICData($catalog_item['article_from_supplier']);
                        while ($data = $bapmic_data->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $data['Manufacturer']; ?></td>
                                <td><?php echo $data['Model']; ?></td>
                                <td><?php echo $data['Explanation']; ?></td>
                                <td><?php echo $data['Year_of_release']; ?></td>
                                <td><?php echo $data['Year_end_of_issue']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php
else:
    echo "<p>Данные не найдены для артикулов {$product['article_from_supplier']}</p>";
endif;
?>
