<?php
// 设置响应头
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 如果不是POST请求，返回错误
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// 获取请求体
$data = json_decode(file_get_contents('php://input'), true);

// 验证请求数据
if (!isset($data['prompt'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing prompt parameter']);
    exit;
}

// 提取请求参数
$prompt = $data['prompt'];

// 调用DeepSeek API
try {
    $apiKey = 'sk-6836e441bf8840fbbc9acbb97b35fd13'; // 替换为实际的DeepSeek API密钥
    
    // 构建API请求
    $apiUrl = 'https://api.deepseek.com/v1/chat/completions';
    $requestData = [
        'model' => 'deepseek-coder',  // 使用更强大的模型
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a specialized baby name generator with extensive knowledge of names from all cultures, origins, and styles. When asked to generate names, return ONLY a valid JSON array of names with no additional text, explanation or formatting. Example response format: ["Name1","Name2","Name3","Name4","Name5","Name6"]'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.8,  // 增加创造性
        'max_tokens' => 200,   // 增加输出长度
        'response_format' => ['type' => 'json_object']
    ];
    
    // 设置cURL请求
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    // 发送请求
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // 检查请求是否成功
    if ($httpCode !== 200) {
        throw new Exception('API request failed with status code ' . $httpCode);
    }
    
    // 解析API响应
    $responseData = json_decode($response, true);
    
    // 提取生成的名字
    if (isset($responseData['choices'][0]['message']['content'])) {
        $generatedContent = $responseData['choices'][0]['message']['content'];
        
        // 记录原始响应以便调试
        file_put_contents('api_response_log.txt', date('Y-m-d H:i:s') . ' - ' . $generatedContent . PHP_EOL, FILE_APPEND);
        
        // 尝试解析JSON响应
        $namesData = json_decode($generatedContent, true);
        
        // 检查是否有names字段或直接是数组
        if (is_array($namesData)) {
            if (isset($namesData['names']) && is_array($namesData['names'])) {
                $names = $namesData['names'];
            } else {
                // 假设直接是名字数组
                $names = array_values($namesData);
            }
        } else {
            // 如果不是有效JSON，尝试从文本中提取名字
            preg_match_all('/[A-Z][a-zéèêëàâäôöùûüÿçæœ\-\']+/', $generatedContent, $matches);
            $names = array_slice(array_unique($matches[0]), 0, 6);
        }
    } else {
        // 如果响应格式不正确，使用默认名字
        $names = [];
    }
    
    // 确保我们有足够的名字
    if (empty($names) || count($names) < 6) {
        // 提取请求中的参数以选择适当的默认名字
        $gender = 'girl'; // 默认为女孩名字
        if (stripos($prompt, 'boy names') !== false) {
            $gender = 'boy';
        } elseif (stripos($prompt, 'unisex names') !== false) {
            $gender = 'unisex';
        }
        
        $defaultNames = [];
        if ($gender === 'boy') {
            $defaultNames = ['Ethan', 'Oliver', 'William', 'James', 'Benjamin', 'Lucas'];
        } elseif ($gender === 'unisex') {
            $defaultNames = ['Alex', 'Jordan', 'Riley', 'Taylor', 'Casey', 'Morgan'];
        } else {
            $defaultNames = ['Aria', 'Luna', 'Nova', 'Stella', 'Willow', 'Ivy'];
        }
        
        // 合并现有名字和默认名字
        $names = array_merge($names, array_slice($defaultNames, 0, 6 - count($names)));
    }
    
    // 确保只返回6个名字
    $names = array_slice($names, 0, 6);
    
    // 返回结果
    echo json_encode(['names' => $names]);
    
} catch (Exception $e) {
    // 返回错误信息
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 