<script>
    // Скрипт для переключения фото
    let currentPhotoIndex = 0;
    const photoList = Array.from(document.querySelectorAll('.small-photo')).map(img => img.src);

    function showOverlay(src) {
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = 0;
        overlay.style.left = 0;
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
        overlay.style.display = 'flex';
        overlay.style.justifyContent = 'center';
        overlay.style.alignItems = 'center';
        overlay.style.zIndex = 1000;

        const img = document.createElement('img');
        img.src = src;
        img.style.maxWidth = '80%';
        img.style.maxHeight = '80%';
        img.style.border = '5px solid #fff';

        const closeButton = document.createElement('div');
        closeButton.textContent = 'X';
        closeButton.style.position = 'absolute';
        closeButton.style.top = '20px';
        closeButton.style.right = '20px';
        closeButton.style.color = '#fff';
        closeButton.style.cursor = 'pointer';
        closeButton.style.fontSize = '24px';
        closeButton.addEventListener('click', function() {
            overlay.remove();
        });

        const prevButton = document.createElement('div');
        prevButton.textContent = 'назад';
        prevButton.style.position = 'absolute';
        prevButton.style.left = '20px';
        prevButton.style.top = '50%';
        prevButton.style.transform = 'translateY(-50%)';
        prevButton.style.color = '#fff';
        prevButton.style.cursor = 'pointer';
        prevButton.style.fontSize = '24px';
        prevButton.addEventListener('click', function() {
            currentPhotoIndex = (currentPhotoIndex - 1 + photoList.length) % photoList.length;
            img.src = photoList[currentPhotoIndex];
        });

        const nextButton = document.createElement('div');
        nextButton.textContent = 'вперед';
        nextButton.style.position = 'absolute';
        nextButton.style.right = '20px';
        nextButton.style.top = '50%';
        nextButton.style.transform = 'translateY(-50%)';
        nextButton.style.color = '#fff';
        nextButton.style.cursor = 'pointer';
        nextButton.style.fontSize = '24px';
        nextButton.addEventListener('click', function() {
            currentPhotoIndex = (currentPhotoIndex + 1) % photoList.length;
            img.src = photoList[currentPhotoIndex];
        });

        overlay.appendChild(img);
        overlay.appendChild(closeButton);
        overlay.appendChild(prevButton);
        overlay.appendChild(nextButton);

        document.body.appendChild(overlay);
    }

    document.querySelectorAll('.small-photo').forEach((img, index) => {
        img.addEventListener('click', function() {
            currentPhotoIndex = index;
            showOverlay(this.src);
        });
    });

    document.querySelectorAll('.product-photo').forEach(img => {
        img.addEventListener('click', function() {
            const src = this.src;
            currentPhotoIndex = photoList.indexOf(src);
            showOverlay(src);
        });
    });
</script>
