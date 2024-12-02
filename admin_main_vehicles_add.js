document.getElementById('mainImage').addEventListener('change', function(event) {
    previewImage(event, 'mainImagePreview');
});

document.getElementById('frontImage').addEventListener('change', function(event) {
    previewImage(event, 'frontImagePreview');
});

document.getElementById('passengerImage').addEventListener('change', function(event) {
    previewImage(event, 'passengerImagePreview');
});

function previewImage(event, previewId) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewElement = document.getElementById(previewId);
            previewElement.src = e.target.result;
            previewElement.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}