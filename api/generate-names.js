// Vercel Serverless Function for DeepSeek API
const fetch = (...args) => import('node-fetch').then(({default: fetch}) => fetch(...args));

module.exports = async (req, res) => {
  // 设置 CORS 头
  res.setHeader('Access-Control-Allow-Credentials', true);
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET,OPTIONS,POST');
  res.setHeader('Access-Control-Allow-Headers', 'X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version');

  // 处理 OPTIONS 请求
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  try {
    // 获取请求体
    const { prompt } = req.body;
    
    if (!prompt) {
      return res.status(400).json({ error: 'Missing prompt parameter' });
    }

    // DeepSeek API 配置
    const apiKey = process.env.DEEPSEEK_API_KEY; // 从环境变量获取
    const apiEndpoint = 'https://api.deepseek.com/v1/chat/completions';

    // 调用 DeepSeek API
    const response = await fetch(apiEndpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${apiKey}`
      },
      body: JSON.stringify({
        model: 'deepseek-chat',
        messages: [
          {
            role: 'user',
            content: prompt
          }
        ],
        temperature: 0.7,
        max_tokens: 300
      })
    });

    const data = await response.json();
    
    if (!response.ok) {
      return res.status(response.status).json({ 
        error: `DeepSeek API error: ${data.error?.message || 'Unknown error'}` 
      });
    }

    // 解析 DeepSeek 响应
    const content = data.choices?.[0]?.message?.content || '';
    
    try {
      // 尝试解析返回的 JSON 数组
      const names = JSON.parse(content);
      if (Array.isArray(names)) {
        return res.status(200).json({ names });
      } else {
        return res.status(200).json({ names: [] });
      }
    } catch (e) {
      return res.status(200).json({ 
        error: 'Failed to parse names from response',
        content 
      });
    }
  } catch (error) {
    return res.status(500).json({ error: `Server error: ${error.message}` });
  }
}; 