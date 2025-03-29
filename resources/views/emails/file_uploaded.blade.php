<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Uploaded Successfully</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-orange-400 to-orange-300 font-sans leading-relaxed min-h-screen flex items-center justify-center">

    <div class="max-w-md mx-auto p-6 bg-white rounded-2xl shadow-xl">
        <div class="text-center mb-6">
            <h1 class="text-4xl font-extrabold text-orange-500">File Uploaded Successfully!</h1>
        </div>

        <div class="mb-4">
            <p class="text-gray-800">Hello,</p>
            <p class="text-gray-700">We are excited to inform you that your file <span class="font-semibold">"{{ $fileName }}"</span> has been successfully uploaded to our system.</p>
        </div>

        <div class="bg-orange-100 p-4 rounded-lg shadow-md mb-4">
            <h2 class="text-lg font-semibold text-orange-600 mb-2">File Details:</h2>
            <ul class="space-y-2">
                <li><span class="font-semibold text-orange-500">File Name:</span> {{ $fileName }}</li>
                <li><span class="font-semibold text-orange-500">File Size:</span> {{ $fileSize }} bytes</li>
                <li><span class="font-semibold text-orange-500">MIME Type:</span> {{ $fileMimeType }}</li>
            </ul>
        </div>

        <div class="text-center">
            <a href="{{ $downloadUrl }}" target="_blank" class="bg-orange-500 text-white py-2 px-6 rounded-full shadow-lg hover:bg-orange-600 transition-transform transform hover:scale-105">Download File</a>
        </div>

        <div class="mt-6 text-center text-sm text-gray-600">
            <p>If you did not upload this file or have any questions, please feel free to contact our support team.</p>
            <a href="mailto:support@filesharing.com" class="text-orange-500 hover:underline">Contact Support</a>
        </div>

        <div class="mt-4 text-center text-xs text-gray-400">&copy; 2025 Tina Mart Tenanet File Sharing</div>
    </div>

</body>
</html>