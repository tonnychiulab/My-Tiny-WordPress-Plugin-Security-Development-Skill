# WordPress Plugin Security Development Skill

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![Security](https://img.shields.io/badge/Security-Best%20Practices-green.svg)]()

> 基於 WPScan 和 Patchstack 真實漏洞案例的 WordPress 外掛安全開發指南

## 📋 目錄

- [簡介](#簡介)
- [為什麼需要這個 Skill?](#為什麼需要這個-skill)
- [特色功能](#特色功能)
- [資料來源](#資料來源)
- [內容概覽](#內容概覽)
- [快速開始](#快速開始)
- [使用方式](#使用方式)
- [文件結構](#文件結構)
- [漏洞統計](#漏洞統計)
- [貢獻指南](#貢獻指南)
- [授權](#授權)
- [致謝](#致謝)

## 🎯 簡介

這是一份全面的 WordPress 外掛安全開發指南,整合了 **WPScan Vulnerability Database** 和 **Patchstack Database** 的真實漏洞案例,幫助開發者在撰寫 WordPress 外掛時預防常見的安全漏洞。

## 🤔 為什麼需要這個 Skill?

根據 **Patchstack 2024-2025 年度報告**:
- WordPress 生態系統中披露了超過 **5,000+ 個漏洞**
- **42.69%** 的漏洞是 Cross-Site Scripting (XSS)
- **14.79%** 是 Cross-Site Request Forgery (CSRF)
- **11.36%** 是權限控制問題 (Broken Access Control)
- **6.29%** 是 SQL Injection

大多數漏洞都是因為開發者不了解安全最佳實踐而產生的,這份指南將幫助你避免重複這些錯誤。

## ✨ 特色功能

- ✅ **真實案例分析** - 基於 WPScan 和 Patchstack 的實際漏洞
- ✅ **對比範例** - 每個安全問題都有「危險寫法」vs「安全寫法」
- ✅ **完整涵蓋** - SQL Injection、XSS、CSRF、權限控制、檔案上傳等
- ✅ **即用程式碼** - 可直接複製使用的安全程式碼範例
- ✅ **快速參考** - 函數速查表和決策樹
- ✅ **AI 友善** - 特別設計與 AI 助手 (如 Claude、ChatGPT) 協作
- ✅ **中文撰寫** - 完整繁體中文文件

## 📊 資料來源

### WPScan Vulnerability Database
- **網站**: https://wpscan.com/
- **涵蓋**: 21,000+ 已知安全漏洞
- **內容**: WordPress 核心、外掛和主題漏洞
- **特色**: 由 WordPress 安全專家手動驗證

### Patchstack Database
- **網站**: https://patchstack.com/database/
- **涵蓋**: 2024 年披露 5,000+ 個漏洞
- **內容**: 手工策劃和驗證的漏洞資訊
- **特色**: 提供詳細的漏洞細節和修復建議

## 📚 內容概覽

### 核心章節

1. **SQL Injection 防護**
   - `$wpdb->prepare()` 使用方法
   - WordPress 內建函數的安全使用
   - LIKE 查詢的安全處理

2. **Cross-Site Scripting (XSS) 防護**
   - 輸出轉義函數完整指南
   - 根據上下文選擇正確函數
   - 允許特定 HTML 的安全方法

3. **Cross-Site Request Forgery (CSRF) 防護**
   - WordPress Nonces 完整指南
   - 表單、URL、AJAX 的 CSRF 防護
   - REST API 中的 Nonce 使用

4. **Broken Access Control (權限控制)**
   - `current_user_can()` 使用方法
   - WordPress 權限系統深入解析
   - REST API 權限檢查

5. **Sensitive Data Exposure (敏感資料洩露)**
   - API 金鑰保護
   - 敏感資料加密
   - 安全的錯誤處理

6. **File Upload 安全**
   - 檔案類型驗證
   - 圖片上傳的安全處理
   - `wp_handle_upload()` 使用方法

7. **資料淨化與驗證**
   - 完整的淨化函數列表
   - 驗證函數實踐
   - 輸入處理最佳實踐

8. **真實漏洞案例**
   - 基於 WPScan 和 Patchstack 的案例
   - 漏洞分析與修復方案
   - 預防類似問題的方法

9. **開發工具與資源**
   - WPScan CLI 使用
   - PHP_CodeSniffer 配置
   - 自動化安全檢查

10. **持續安全實踐**
    - 開發、部署、維護階段的安全措施
    - 監控與日誌
    - 備份策略

## 🚀 快速開始

### 1. 克隆倉庫

```bash
git clone https://github.com/your-username/wordpress-plugin-security-skill.git
cd wordpress-plugin-security-skill
```

### 2. 閱讀文件

```bash
# 主要技能文件
cat SKILL.md

# 快速參考指南
cat WordPress-Security-Quick-Reference.md

# 使用說明
cat How-to-Use-This-Skill.md
```

### 3. 整合到專案

```bash
# 複製到你的專案文件目錄
cp SKILL.md /path/to/your/project/docs/

# 或建立符號連結
ln -s $(pwd)/SKILL.md /path/to/your/project/docs/
```

## 💻 使用方式

### 作為開發規範

將 `SKILL.md` 作為團隊的安全開發規範:

```markdown
# 團隊規範
所有 WordPress 外掛開發必須遵循 WordPress Plugin Security Development Skill 中的安全準則。

在 Code Review 時,請參考 SKILL.md 檢查:
- [ ] SQL 查詢是否使用 prepare()
- [ ] 輸出是否正確轉義
- [ ] 表單是否有 nonce 驗證
- [ ] 操作是否檢查權限
- [ ] 檔案上傳是否安全
```

### 與 AI 協作

在使用 AI 助手 (如 Claude、ChatGPT) 開發時:

```
請按照 WordPress Plugin Security Development Skill 的安全準則,
幫我建立一個使用者註冊表單功能,需要包含:
1. 表單 HTML
2. 資料驗證與淨化
3. CSRF 防護
4. 資料庫儲存
5. 所有必要的安全檢查
```

### 程式碼審查

使用 `WordPress-Security-Quick-Reference.md` 進行快速檢查:

```php
// 檢查清單
✅ 輸入淨化: sanitize_text_field()
✅ 輸出轉義: esc_html()
✅ SQL 安全: $wpdb->prepare()
✅ CSRF 防護: wp_nonce_field() + wp_verify_nonce()
✅ 權限檢查: current_user_can()
```

### WPScan API 整合

```php
// 使用 WPScan API 檢查外掛漏洞
function check_plugin_security($plugin_slug) {
    $api_token = 'YOUR_API_TOKEN'; // 從 https://wpscan.com/ 取得
    $url = "https://wpscan.com/api/v3/plugins/{$plugin_slug}";
    
    $response = wp_remote_get($url, array(
        'headers' => array('Authorization' => 'Token ' . $api_token)
    ));
    
    // 處理回應...
}
```

## 📁 文件結構

```
wordpress-plugin-security-skill/
├── README.md                           # 本文件
├── SKILL.md                            # 主要技能文件
├── WordPress-Security-Quick-Reference.md  # 快速參考指南
├── How-to-Use-This-Skill.md           # 使用說明
└── LICENSE                             # MIT 授權
```

## 📈 漏洞統計

根據 **Patchstack 2024-2025** 年度統計:

| 漏洞類型 | 佔比 | 說明 |
|---------|------|------|
| Cross-Site Scripting (XSS) | 42.69% | 未正確轉義輸出 |
| 其他漏洞 | 16.58% | 各種其他安全問題 |
| Cross-Site Request Forgery (CSRF) | 14.79% | 缺少 nonce 驗證 |
| Broken Access Control | 11.36% | 權限檢查不完整 |
| SQL Injection | 6.29% | 未使用 prepare() |
| Sensitive Data Exposure | 5.51% | 敏感資料洩露 |
| Arbitrary File Upload | 2.77% | 檔案上傳驗證不足 |

## 🛠️ 開發工具推薦

### 安全掃描工具

```bash
# WPScan CLI
gem install wpscan
wpscan --url https://your-site.com --api-token YOUR_TOKEN

# PHP_CodeSniffer with WordPress Coding Standards
composer require --dev squizlabs/php_codesniffer
composer require --dev wp-coding-standards/wpcs
./vendor/bin/phpcs --standard=WordPress your-plugin/
```

### IDE 擴充套件

- **VS Code**: 
  - PHP Intelephense
  - WordPress Snippets
  - PHP Security Vulnerabilities
  
- **PhpStorm**:
  - WordPress Support
  - PHP Security Audit

## 🤝 貢獻指南

我們歡迎各種形式的貢獻!

### 如何貢獻

1. Fork 這個倉庫
2. 建立你的特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交你的變更 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 開啟一個 Pull Request

### 貢獻類型

- 🐛 回報安全漏洞或錯誤
- 📝 改進文件
- ✨ 新增漏洞案例
- 🔧 更新最佳實踐
- 🌍 翻譯成其他語言

### 回報漏洞

如果你發現新的安全漏洞案例,請:

1. 在 Issues 中建立新的問題
2. 使用標籤 `security-case`
3. 提供漏洞類型、影響版本、修復方案

## 📋 待辦事項

- [ ] 新增更多真實漏洞案例
- [ ] 建立英文版本文件
- [ ] 開發 VS Code 擴充套件
- [ ] 建立自動化檢查腳本
- [ ] 整合 GitHub Actions 範例
- [ ] 新增影片教學連結

## 📖 延伸閱讀

### 官方文件
- [WordPress Plugin Security](https://developer.wordpress.org/plugins/security/)
- [WordPress Theme Security](https://developer.wordpress.org/themes/advanced-topics/security/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)

### 安全資源
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [WordPress Security Whitepaper](https://wordpress.org/about/security/)
- [WPScan Blog](https://blog.wpscan.com/)
- [Patchstack Blog](https://patchstack.com/blog/)

### 學習平台
- [WordPress.tv Security Videos](https://wordpress.tv/?s=security)
- [WP Security Weekly Podcast](https://wpsecurityweekly.com/)

## 📞 聯絡方式

- **Issues**: [GitHub Issues](https://github.com/your-username/wordpress-plugin-security-skill/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-username/wordpress-plugin-security-skill/discussions)

## 📄 授權

本專案採用 MIT 授權 - 詳見 [LICENSE](LICENSE) 文件

## 🙏 致謝

- 感謝 [WPScan](https://wpscan.com/) 提供詳細的漏洞資料庫
- 感謝 [Patchstack](https://patchstack.com/) 提供漏洞統計和案例分析
- 感謝 WordPress 安全社群的持續努力
- 感謝所有貢獻者的付出

## ⭐ Star History

如果這個專案對你有幫助,請給我們一個 Star!

[![Star History Chart](https://api.star-history.com/svg?repos=your-username/wordpress-plugin-security-skill&type=Date)](https://star-history.com/#your-username/wordpress-plugin-security-skill&Date)

---

**記住:安全不是一次性的工作,而是一個持續的過程!** 🔐

Made with ❤️ by the WordPress Security Community
