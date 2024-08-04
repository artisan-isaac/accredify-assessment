<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>

    <body>
        <h1>Upload an OpenAttestation(.oa) file</h1>

        <p>An .oa file is the verifiable format of a digital document. To verify, request for an .oa file from your issuer, client, or partner.</p>

        <p>If you do not have access to an .oa file, please visit this link to understand how the verification process occurs.</p>

        <p><a href="#">Learn more about document verification here</a></p>

        <form id="upload-form">
            @csrf
            <div id="drop-area" class="upload-area" style="border: 2px dashed #ccc; padding: 20px; text-align: center;">
                <svg width="100" height="100" viewBox="0 0 100 100">
                    <path d="M50 20 L80 80 L20 80 Z" fill="#D3D3D3" />
                    <path d="M35 50 L65 50 L50 35 Z" fill="white" />
                </svg>

                <p>Drag & drop your verifiable file</p>
                <p>(.trustdoc, .opencert, .oa, .pdf, .png, .svg)</p>
                <p>or <label for="fileElem" style="color: #00BFFF; cursor: pointer;">browse</label> to choose a file</p>
                <input type="file" id="fileElem" name="file" accept=".trustdoc,.opencert,.oa,.pdf,.png,.svg" style="display:none">
            </div>
        </form>

        <div id="apiDataContainer"></div>
    </body>
</html>

<script>
    let dropArea = document.getElementById('drop-area');
    let uploadForm = document.getElementById('upload-form');
    let fileInput = document.getElementById('fileElem');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropArea.classList.add('highlight');
    }

    function unhighlight(e) {
        dropArea.classList.remove('highlight');
    }

    dropArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        let dt = e.dataTransfer;
        let files = dt.files;

        handleFiles(files);
    }

    function handleFiles(files) {
        fileInput.files = files;
        uploadFile();
    }

    fileInput.addEventListener('change', function() {
        uploadFile();
    });

    function uploadFile() {
        const formData = new FormData(uploadForm);

        fetch('{{ url('api/verify') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Authorization': "Bearer {{ session('token') }}"
            },
            credentials: 'include'  // This is important for including session cookies
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            const apiDataContainer = document.getElementById('apiDataContainer');
            apiDataContainer.innerHTML = JSON.stringify(data, null, 2);
        })
        .catch(error => {
            console.error('Error:', error);
            const apiDataContainer = document.getElementById('apiDataContainer');
            apiDataContainer.innerHTML = JSON.stringify(data, null, 2);
        });
    }


</script>
