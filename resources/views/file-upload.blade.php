<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel File Upload</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800">
    <div class="max-w-2xl mx-auto p-8">
        <h1 class="text-3xl font-semibold text-center mb-6">Upload Your File</h1>

        <!-- File Upload Form -->
        <form id="fileUploadForm" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-lg">
            @csrf
            <div class="mb-4">
                <label for="file" class="block text-lg font-medium text-gray-700">Choose a file</label>
                <input type="file" name="file" id="file" required
                    class="mt-2 block w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Upload
                </button>
                <button type="button" id="cancelUploadBtn" class="px-6 py-2 bg-red-600 text-white font-semibold rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 hidden">
                    Cancel
                </button>
            </div>
        </form>

        <!-- Progress Bar -->
        <div id="progressContainer" class="mt-4 hidden">
            <div class="flex items-center justify-between">
                <span>Uploading...</span>
                <span id="progressText">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                <div id="progressBar" class="bg-blue-600 h-2 rounded-full" style="width: 0;"></div>
            </div>
        </div>

        <!-- Display Status or Error Message -->
        <div id="statusMessage" class="mt-4 text-center text-lg"></div>
    </div>

    <script>
        $(document).ready(function () {
            // Handle form submission
            $('#fileUploadForm').on('submit', function (e) {
                e.preventDefault(); // Prevent the form from submitting normally
                var formData = new FormData(this); // Get form data
                var file = $('#file')[0].files[0];

                // Show the uploading status and progress bar
                $('#progressContainer').removeClass('hidden');
                $('#statusMessage').text('Uploading...');
                $('#progressBar').css('width', '0%');
                $('#progressText').text('0%');
                $('#cancelUploadBtn').removeClass('hidden');

                // Step 1: Get Presigned URL from backend
                axios.post('/get-presigned-url', {
                    file_name: file.name,
                    file_type: file.type
                })
                    .then(function (response) {
                        var presignedUrl = response.data.presigned_url;
                        console.log(presignedUrl);

                        // Step 2: Upload the file using the presigned URL
                        var config = {
                            headers: {
                                'Content-Type': file.type
                            },
                            onUploadProgress: function (progressEvent) {
                                if (progressEvent.lengthComputable) {
                                    let percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                                    $('#progressBar').css('width', percentCompleted + '%');
                                    $('#progressText').text(percentCompleted + '%');
                                }
                            }
                        };

                        axios.put(presignedUrl, file, config)
                            .then(function () {
                                // Step 3: Store file metadata in the backend
                                axios.post('/store-file-metadata', {
                                    file_path: presignedUrl.split('?')[0],  // Extract the path from the URL
                                    file_name: file.name,
                                    file_size: file.size,
                                    mime_type: file.type
                                })
                                    .then(function (response) {
                                        // On success, show success message
                                        $('#statusMessage').text('File uploaded and metadata stored successfully.');
                                        $('#fileUploadForm')[0].reset(); // Reset the form
                                        $('#progressContainer').addClass('hidden');
                                    })
                                    .catch(function (error) {
                                        // On error, show error message
                                        $('#statusMessage').text('Failed to store file metadata: ' + error.response.data.error);
                                        $('#progressContainer').addClass('hidden');
                                    });
                            })
                            .catch(function (error) {
                                // On error, show error message
                                $('#statusMessage').text('File upload failed: store meta data ' + error);
                                $('#progressContainer').addClass('hidden');
                            });
                    })
                    .catch(function (error) {
                        // On error, show error message
                        $('#statusMessage').text('Failed to get presigned URL: ' + error.response.data);
                        $('#progressContainer').addClass('hidden');
                    });
            });

            // Handle cancel upload
            $('#cancelUploadBtn').on('click', function () {
                // Cancel the upload and reset UI
                $('#statusMessage').text('Upload canceled.');
                $('#progressContainer').addClass('hidden');
                $('#fileUploadForm')[0].reset();
            });
        });
    </script>
</body>

</html>
