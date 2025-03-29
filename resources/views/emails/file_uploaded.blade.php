<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Uploaded Successfully</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            color: #4CAF50;
        }
        .email-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="email-content">
        <h1>File Uploaded Successfully</h1>
        <p>Hi,</p>
        <p>We're excited to inform you that your file "<strong>{{ $fileName }}</strong>" has been successfully uploaded to our system.</p>
        
        <p><strong>File Details:</strong></p>
        <ul>
            <li><strong>File Name:</strong> {{ $fileName }}</li>
            <li><strong>File Size:</strong> {{ $fileSize }} bytes</li>
            <li><strong>MIME Type:</strong> {{ $fileMimeType }}</li>
        </ul>

        <p>To download the file, please click the link below:</p>
        <p>
            <a href="{{ $downloadUrl }}" target="_blank">Download File</a>
        </p>

        <p>If you didn't upload this file or have any questions, please contact support.</p>

        <p>Thanks, <br> The File Sharing Team</p>
    </div>

</body>
</html>
