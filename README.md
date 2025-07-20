# ConvoyPanel WHMCS Server Module

![ConvoyPanel](https://img.shields.io/badge/ConvoyPanel-v3%20%7C%20v4-blue)
![WHMCS](https://img.shields.io/badge/WHMCS-7.x%20%7C%208.x-green)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)
![License](https://img.shields.io/badge/License-MIT-yellow)

一個功能完整的 WHMCS 伺服器模組，用於與 ConvoyPanel 整合，提供自動化的 VPS/伺服器開通、管理和計費功能。

## 📋 目錄

- [功能特色](#-功能特色)
- [系統需求](#-系統需求)
- [快速開始](#-快速開始)
- [安裝步驟](#-安裝步驟)
- [配置說明](#-配置說明)
- [使用指南](#-使用指南)
- [故障排除](#-故障排除)

## ✨ 功能特色

### 🚀 自動化管理
- **一鍵開通** - 根據產品配置自動創建 VPS
- **智能暫停** - 支援服務暫停和自動恢復
- **安全終止** - 完全刪除伺服器和相關數據
- **用戶同步** - 自動創建或關聯 ConvoyPanel 用戶帳號

### 💻 用戶體驗
- **客戶端面板** - 在 WHMCS 客戶區顯示伺服器詳細資訊
- **單點登入** - 直接從 WHMCS 跳轉到 ConvoyPanel
- **即時狀態** - 顯示伺服器運行狀態和資源使用情況
- **IP 管理** - 自動分配和顯示 IP 地址

### 🔧 管理功能
- **連接測試** - 一鍵驗證 API 連接狀態
- **管理員連結** - 快速存取伺服器管理介面
- **錯誤日誌** - 詳細的錯誤記錄和除錯資訊
- **安全驗證** - API Token 安全驗證機制

## 🔧 系統需求

| 組件 | 版本需求 |
|------|----------|
| **WHMCS** | 7.x, 8.x |
| **PHP** | 7.4 或更高版本 |
| **ConvoyPanel** | v3, v4 |
| **cURL** | 啟用 cURL 擴展 |
| **OpenSSL** | 支援 HTTPS 連接 |

### PHP 擴展需求
```bash
php-curl
php-json
php-openssl
```

## 🚀 快速開始

### 1️⃣ 下載模組
```bash
# 克隆儲存庫
git clone https://github.com/your-repo/convoy-whmcs-module.git

# 或直接下載 ZIP 檔案
wget https://github.com/your-repo/convoy-whmcs-module/archive/main.zip
```

### 2️⃣ 安裝檔案
```
將 convoy 資料夾全部複製到 /path/to/whmcs/module/server
```

### 3️⃣ 配置 ConvoyPanel
1. 登入 ConvoyPanel 管理面板
2. 生成新的 API Token
3. 記錄 Token 供 WHMCS 使用

### 4️⃣ 配置 WHMCS
1. 前往 **Configuration > System Settings > Servers**
2. 新增伺服器，選擇 **ConvoyPanel** 模組
3. 輸入 ConvoyPanel URL 和 API Token
4. 測試連接並儲存

## 📦 安裝步驟

### 詳細安裝指南

#### 步驟 1: 檔案部署
```
WHMCS 根目錄/
├── modules/
│   └── servers/
│       └── convoy/
│           ├── convoy.php                # 主模組檔案
│           └── templates/
│               └── clientarea.tpl        # 客戶端模板
```

#### 步驟 2: ConvoyPanel 設定
1. **生成 API Token**
   ```
   ConvoyPanel Admin → API → Create Token
   權限: Application API
   ```

2. **記錄必要資訊**
   - ConvoyPanel URL: `https://panel.yourdomain.com`
   - API Token: `生成的 Token 字串`
   - Node ID: 在 Nodes 管理頁面查看
   - Template UUID: 在 Templates 管理頁面查看

#### 步驟 3: WHMCS 伺服器配置
1. 前往 WHMCS 管理面板
2. **Configuration → System Settings → Servers**
3. 點擊 **Add New Server**
4. 填寫設定：

| 欄位 | 值 | 說明 |
|------|----|----- |
| Name | ConvoyPanel Server 1 | 伺服器識別名稱 |
| Hostname | https://panel.yourdomain.com | ConvoyPanel 完整 URL |
| IP Address | 1.2.3.4 | ConvoyPanel 伺服器 IP |
| Password | your_api_token_here | API Token |
| Port | 443 (HTTPS) / 80 (HTTP) | 連接埠 |
| Secure | ✅ (如使用 HTTPS) | 安全連接 |

5. 點擊 **Test Connection** 驗證
6. 儲存配置

#### 步驟 4: 產品設定
1. **Configuration → System Settings → Products/Services**
2. 創建新產品或編輯現有產品
3. **Module Settings** 標籤：
   - Module Name: **ConvoyPanel**
   - Server Group: 選擇包含 ConvoyPanel 伺服器的群組

## ⚙️ 配置說明

### 產品配置選項

| 配置選項 | 類型 | 說明 | 範例值 |
|----------|------|------|--------|
| **CPU Cores** | 整數 | CPU 核心數量 | `2` |
| **Memory (MB)** | 整數 | 記憶體大小（MB） | `2048` |
| **Disk Space (MB)** | 整數 | 磁碟空間（MB） | `20480` |
| **Bandwidth (GB)** | 整數/空白 | 月頻寬限制，空白=無限 | `1000` |
| **Snapshots** | 整數 | 快照數量限制 | `5` |
| **Backups** | 整數/空白 | 備份數量限制，空白=無限 | `5` |
| **Template UUID** | 字串 | ConvoyPanel 模板 UUID | `d176b498-87e8...` |
| **Node ID** | 整數 | 部署節點 ID | `1` |

### 取得配置資訊

#### 🔍 獲取 Template UUID
```bash
# 方法 1: ConvoyPanel 管理介面
Admin Panel → Templates → 選擇模板 → 複製 UUID

# 方法 2: API 查詢
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json" \
     https://panel.yourdomain.com/api/application/templates
```

#### 🔍 獲取 Node ID
```bash
# 方法 1: ConvoyPanel 管理介面  
Admin Panel → Nodes → 查看 ID 欄位

# 方法 2: API 查詢
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json" \
     https://panel.yourdomain.com/api/application/nodes
```

## 📖 使用指南

### 管理員操作

#### 🔧 伺服器管理
1. **查看服務狀態**
   ```
   WHMCS Admin → Clients → Products/Services → 選擇服務
   ```

2. **手動操作**
   - 立即開通: Actions → Create
   - 暫停服務: Actions → Suspend  
   - 取消暫停: Actions → Unsuspend
   - 終止服務: Actions → Terminate

3. **快速連結**
   - 點擊 "Manage Server" 直接跳轉到 ConvoyPanel

#### 📊 監控和日誌
```
Configuration → System Logs → Module Log
搜尋關鍵字: "convoy"
```

### 客戶端功能

#### 💻 客戶區面板
客戶登入後可在服務詳情頁面看到：

1. **伺服器資訊**
   - 伺服器名稱和主機名
   - 當前狀態（運行/停止/安裝中）
   - VM ID

2. **資源配置**
   - CPU 核心數
   - 記憶體大小
   - 磁碟空間

3. **網路資訊**
   - 分配的 IPv4/IPv6 地址
   - CIDR 和網關資訊

4. **管理功能**
   - 直接存取 ConvoyPanel
   - 基本控制按鈕（啟動/重啟/停止）

#### 🔐 單點登入
```
點擊 "一鍵登入" → 自動跳轉並登入
無需額外輸入帳號密碼
```

## 🔧 故障排除

### 常見問題

#### ❌ 連接問題
**錯誤**: "Connection failed"

**解決方案**:
```bash
# 1. 檢查 URL 格式
正確: https://panel.yourdomain.com
錯誤: panel.yourdomain.com

# 2. 驗證 API Token
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json" \
     https://panel.yourdomain.com/api/application/users

# 3. 檢查防火牆
# 確保 WHMCS 伺服器可以連接到 ConvoyPanel
telnet panel.yourdomain.com 443
```

#### ❌ 創建失敗
**錯誤**: "Server creation failed"

**檢查清單**:
- [ ] Template UUID 是否存在
- [ ] Node ID 是否正確  
- [ ] 節點是否有足夠資源
- [ ] API Token 權限是否充足

```bash
# 驗證模板
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://panel.yourdomain.com/api/application/templates/UUID

# 檢查節點資源
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://panel.yourdomain.com/api/application/nodes/ID
```

#### ❌ UUID 錯誤
**錯誤**: "Server UUID not found"

**原因**: 伺服器創建失敗但 WHMCS 記錄不完整

**解決方案**:
```sql
-- 檢查資料庫記錄
SELECT * FROM tblhosting WHERE id = SERVICE_ID;

-- 手動清理（謹慎操作）
UPDATE tblhosting 
SET convoy_server_uuid = NULL 
WHERE id = SERVICE_ID;
```

### 日誌分析

#### 🔍 WHMCS 模組日誌
```
位置: Configuration → System Logs → Module Log
關鍵字: convoy
重要欄位: Request, Response, Error
```

#### 啟用詳細日誌
```php
// 在 convoy.php 中添加
function convoy_CreateAccount($params) {
    // 啟用除錯
    $debug = true;
    
    if ($debug) {
        logModuleCall('convoy', 'Debug', $params, 'Debug info', '');
    }
    
    // ... 其他程式碼
}
```

### 程式碼標準
- 遵循 PSR-12 程式碼風格
- 添加適當的註解和文檔
- 確保向後相容性

### 回報問題
使用 GitHub Issues 回報錯誤：
- 提供詳細的錯誤描述
- 包含 WHMCS 和 ConvoyPanel 版本
- 提供相關日誌記錄
- 說明重現步驟

⭐ **如果這個專案對您有幫助，請給我們一個 Star！** ⭐