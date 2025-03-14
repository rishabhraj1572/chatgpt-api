<?php
$chatResponse = "";
$userInput = isset($_POST['user_prompt']) ? $_POST['user_prompt'] : '';
$imageData = isset($_POST['image']) ? $_POST['image'] : '';
$api_key = isset($_POST['api_key']) ? $_POST['api_key'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($api_key)) {
    $prompt = "Provide correct answer for the given MCQ. Example Output: Some solving explanation ... Conclusion : answer is A)option_a ; if answer is not present in all the options then give the nearest answer in the conclusion and also mention Nearest Answer";

    $api_url = 'https://models.inference.ai.azure.com/chat/completions';

    $payload = [
        "model" => "gpt-4o",
        "messages" => [[
            "role" => "user",
            "content" => [["type" => "text", "text" => "$userInput"]]
        ]],
        "max_tokens" => 16384
    ];

    if (!empty($imageData)) {
        $payload["messages"][0]["content"][] = [
            "type" => "image_url",
            "image_url" => ["url" => $imageData]
        ];
    }

    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer $api_key"
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        $chatResponse = "Error: API request failed. Please check your API key and try again.";
    } else {
        $result = json_decode($response, true);
        $chatResponse = isset($result['choices'][0]['message']['content']) ? $result['choices'][0]['message']['content'] : "Error fetching response";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatGPT with Image Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            text-align: center;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .settings-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
        }
        textarea {
            width: 100%;
            height: 100px;
            resize: none;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            margin-top: 10px;
        }

        input[type="file"] {
            width: 100%;
            padding: 5px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            margin-top: 10px;
        }
        button:hover {
            background: #0056b3;
        }
        .mcq-btn {
            font-size: 12px;
            padding: 5px 10px;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .mcq-btn:hover {
            background: #218838;
        }
        #preview {
            max-width: 100%;
            max-height: 200px;
            display: none;
            margin-top: 10px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
        }
        .modal input {
            width: 90%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .modal button {
            width: auto;
            padding: 8px 15px;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <span class="settings-icon" onclick="openSettings()">⚙️</span>
        <h2>ChatGPT with Image Upload</h2>
        <form method="post" id="chat-form" enctype="multipart/form-data">
            <label for="user_prompt">Enter your prompt:</label>
            <button type="button" class="mcq-btn" onclick="insertMCQPrompt()">📌 MCQ Prompt</button>
            <textarea name="user_prompt" id="user_prompt" placeholder="Enter your prompt..."><?php echo htmlspecialchars($userInput); ?></textarea><br>
            
            <label for="attachImage" class="custom-file-upload">Attach a Picture</label>
            <input type="file" id="attachImage" accept="image/*">

            <label for="captureImage" class="custom-file-upload">Click a Picture</label>
            <input type="file" id="captureImage" accept="image/*" capture="environment">

            <style>
            .custom-file-upload {
                padding: 5px;
                background: #007bff;
                color: white;
                border: none;
                cursor: pointer;
                font-size: 16px;
                border-radius: 5px;
                margin-top: 10px;
                display: flow;
            }

            .custom-file-upload:hover {
                background-color: #0056b3;
            }

            #attachImage,#captureImage {
                display: none;
            }
            </style>

            <input type="hidden" name="image" id="imageInput">
            <img id="preview">
            <p>Paste an image using Ctrl+V or Attach an Image</p>
            
            <input type="hidden" name="api_key" id="api_key">
            <button type="submit">Submit</button>
        </form>

        <?php if (!empty($chatResponse)): ?>
            <h3>Response:</h3>
            <p><?php echo nl2br(htmlspecialchars($chatResponse)); ?></p>
        <?php endif; ?>
    </div>

    <div id="settings-modal" class="modal">
        <div class="modal-content">
            <h3>Enter API Key</h3>
            <input type="text" id="apiKeyInput" placeholder="Enter API Key">
            <button onclick="saveApiKey()">Save</button>
            <button onclick="closeSettings()">Cancel</button>
        </div>
    </div>

    <script>
        function insertMCQPrompt() {
            document.getElementById("user_prompt").value = 
                "Provide correct answer for the given MCQ. Example Output: Some solving explanation ... Conclusion : answer is A)option_a ; if answer is not present in all the options then give the nearest answer in the conclusion and also mention Nearest Answer";
        }

        function openSettings() {
            document.getElementById("settings-modal").style.display = "flex";
            document.getElementById("apiKeyInput").value = localStorage.getItem("apiKey") || "";
        }

        function closeSettings() {
            document.getElementById("settings-modal").style.display = "none";
        }

        function saveApiKey() {
            let apiKey = document.getElementById("apiKeyInput").value.trim();
            localStorage.setItem("apiKey", apiKey);
            document.getElementById("api_key").value = apiKey;
            closeSettings();
        }

        document.addEventListener("paste", function (event) {
            let items = (event.clipboardData || event.originalEvent.clipboardData).items;
            for (let item of items) {
                if (item.type.indexOf("image") !== -1) {
                    let file = item.getAsFile();
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        document.getElementById("preview").src = e.target.result;
                        document.getElementById("preview").style.display = "block";
                        document.getElementById("imageInput").value = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        window.onload = function () {
            document.getElementById("api_key").value = localStorage.getItem("apiKey") || "";
        };

        document.getElementById("user_prompt").addEventListener("keydown", function(event) {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault(); // Prevents new line
            document.getElementById("chat-form").submit(); // Submits the form
        }
    });

    window.onload = function () {
    let apiKey = localStorage.getItem("apiKey");
    document.getElementById("api_key").value = apiKey || "";

    // If no API key is stored, open settings modal
    if (!apiKey) {
        openSettings();
    }
};

    </script>
</body>
</html>
