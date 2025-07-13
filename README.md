# 名字生成器

一个使用 DeepSeek API 生成名字的简单应用。

## 功能

- 根据性别和名字来源生成名字
- 响应式设计，适配移动端和桌面端
- 支持多种名字来源：英语、意大利语、法语、西班牙语、希腊语、爱尔兰语、德语、中文、日语

## 技术栈

- 前端：HTML, CSS, JavaScript
- 后端：Node.js (Vercel Serverless Functions)
- API：DeepSeek API

## 部署到 Vercel

1. 克隆项目
   ```
   git clone <仓库地址>
   cd name-generator
   ```

2. 安装依赖
   ```
   npm install
   ```

3. 创建 `.env` 文件并设置 DeepSeek API 密钥
   ```
   DEEPSEEK_API_KEY=your_api_key_here
   ```

4. 本地开发
   ```
   npm run dev
   ```

5. 部署到 Vercel
   ```
   npm run deploy
   ```

## 环境变量

在 Vercel 项目设置中添加以下环境变量：

- `DEEPSEEK_API_KEY`：DeepSeek API 密钥 