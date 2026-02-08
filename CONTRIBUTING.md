# 貢獻指南

感謝你考慮為 WordPress Plugin Security Development Skill 做出貢獻!

## 📋 目錄

- [行為準則](#行為準則)
- [如何貢獻](#如何貢獻)
- [回報問題](#回報問題)
- [提交變更](#提交變更)
- [撰寫規範](#撰寫規範)
- [新增安全案例](#新增安全案例)
- [審查流程](#審查流程)

## 🤝 行為準則

### 我們的承諾

為了營造一個開放和友善的環境,我們承諾:
- 尊重不同的觀點和經驗
- 接受建設性的批評
- 專注於對社群最有利的事情
- 對其他社群成員表現同理心

### 不可接受的行為

- 使用性暗示的語言或圖像
- 騷擾性評論
- 發布他人的私人資訊
- 其他不專業或不受歡迎的行為

## 🎯 如何貢獻

### 貢獻類型

我們歡迎以下類型的貢獻:

#### 1. 🐛 回報安全漏洞或錯誤

如果你發現文件中的錯誤或過時資訊:
- 在 Issues 中建立新問題
- 使用標籤 `bug` 或 `documentation`
- 詳細描述問題

#### 2. ✨ 新增漏洞案例

如果你有新的真實漏洞案例:
- 確認案例來自 WPScan 或 Patchstack
- 提供 CVE 編號或漏洞連結
- 包含漏洞類型、影響版本、修復方案

#### 3. 📝 改進文件

- 修正拼寫或文法錯誤
- 改善程式碼範例
- 增加更清楚的說明
- 優化文件結構

#### 4. 🔧 更新最佳實踐

- 提供新的安全實踐方法
- 更新過時的建議
- 新增實用的工具或資源

#### 5. 🌍 翻譯

- 翻譯成其他語言
- 改進現有翻譯

## 🐛 回報問題

### 回報錯誤

建立 Issue 時請包含:

```markdown
**描述問題**
清楚簡潔地描述錯誤

**重現步驟**
1. 前往 '...'
2. 執行 '...'
3. 看到錯誤

**期望行為**
應該發生什麼

**實際行為**
實際發生什麼

**截圖**
如果適用,請附上截圖

**環境**
- WordPress 版本:
- PHP 版本:
- 瀏覽器:

**其他資訊**
任何其他相關資訊
```

### 建議新功能

```markdown
**功能描述**
清楚描述你想要的功能

**為什麼需要這個功能**
解釋這個功能的用途和好處

**替代方案**
你考慮過的其他解決方案

**其他資訊**
任何其他相關的截圖或說明
```

## 📤 提交變更

### Fork 和 Clone

```bash
# Fork 這個倉庫到你的帳號
# 然後 clone 你的 fork

git clone https://github.com/your-username/wordpress-plugin-security-skill.git
cd wordpress-plugin-security-skill
```

### 建立分支

```bash
# 從 main 分支建立新的特性分支
git checkout -b feature/your-feature-name

# 或修復分支
git checkout -b fix/your-fix-name
```

### 分支命名規範

- `feature/` - 新功能
- `fix/` - 錯誤修復
- `docs/` - 文件更新
- `refactor/` - 重構
- `test/` - 測試相關
- `chore/` - 維護性工作

範例:
- `feature/add-xss-case-study`
- `fix/correct-sql-example`
- `docs/improve-csrf-section`

### 提交訊息規範

使用清楚的提交訊息:

```
<type>: <subject>

<body>

<footer>
```

**Type:**
- `feat`: 新功能
- `fix`: 錯誤修復
- `docs`: 文件變更
- `style`: 格式調整
- `refactor`: 重構
- `test`: 測試
- `chore`: 維護

**範例:**

```
feat: 新增 Arbitrary File Upload 安全案例

- 新增基於 CVE-2024-XXXXX 的案例分析
- 包含危險寫法和安全寫法對比
- 提供完整的驗證流程

Refs #123
```

### 提交你的變更

```bash
# 查看變更
git status

# 暫存變更
git add .

# 提交
git commit -m "feat: 新增 XSS 防護案例"

# 推送到你的 fork
git push origin feature/your-feature-name
```

### 建立 Pull Request

1. 前往你的 fork 在 GitHub 上
2. 點擊 "New Pull Request"
3. 選擇你的分支
4. 填寫 PR 描述:

```markdown
## 變更說明
簡要說明這個 PR 做了什麼

## 變更類型
- [ ] 錯誤修復
- [ ] 新功能
- [ ] 文件更新
- [ ] 重構
- [ ] 其他

## 檢查清單
- [ ] 我已閱讀貢獻指南
- [ ] 程式碼遵循專案的撰寫規範
- [ ] 我已更新相關文件
- [ ] 我已測試變更
- [ ] 所有新舊測試都通過

## 相關 Issue
Closes #123

## 截圖 (如果適用)
附上截圖

## 其他資訊
其他需要說明的內容
```

## 📐 撰寫規範

### Markdown 格式

- 使用 ATX 風格標題 (`#` 不是 `=====`)
- 程式碼區塊使用三個反引號並指定語言
- 清單項目使用 `-` 不是 `*`
- 連結使用相對路徑

### 程式碼範例

所有程式碼範例應該:

1. **完整可執行** - 包含必要的 context
2. **正確縮排** - 使用 4 個空格
3. **加上註解** - 解釋關鍵邏輯
4. **遵循 WordPress Coding Standards**

範例:

```php
/**
 * 安全的使用者資料儲存
 *
 * @param int    $user_id 使用者 ID
 * @param string $field   欄位名稱
 * @param mixed  $value   欄位值
 * @return bool 是否成功
 */
function save_user_data_safely($user_id, $field, $value) {
    // 1. 驗證使用者 ID
    $user_id = absint($user_id);
    
    if ($user_id === 0) {
        return false;
    }
    
    // 2. 淨化欄位名稱
    $field = sanitize_key($field);
    
    // 3. 淨化值
    $value = sanitize_text_field($value);
    
    // 4. 儲存到資料庫
    return update_user_meta($user_id, $field, $value);
}
```

### 文件結構

新增內容時遵循現有結構:

```markdown
## [標題]

### 漏洞成因
簡要說明漏洞如何產生

### ❌ 危險寫法
```php
// 不安全的程式碼範例
```

### ✅ 安全寫法
```php
// 安全的程式碼範例
```

### 關鍵點
- 重點 1
- 重點 2
```

## 🔍 新增安全案例

### 案例來源

案例必須來自可信來源:
- WPScan Vulnerability Database
- Patchstack Database
- WordPress.org 官方安全公告
- MITRE CVE 資料庫

### 案例格式

```markdown
### [漏洞類型] 案例:[簡短標題]

**漏洞描述** (基於 [來源] 案例):
清楚描述漏洞

**CVE 編號**: CVE-XXXX-XXXXX (如果有)
**影響版本**: x.x.x - x.x.x
**修復版本**: x.x.x

**修復方案**:
```php
// ❌ 漏洞版本
[不安全的程式碼]

// ✅ 修復版本
[安全的程式碼]
```

**學習重點**:
- 重點 1
- 重點 2
```

### 案例檢查清單

在提交新案例前確認:

- [ ] 案例來自可信來源
- [ ] 提供 CVE 或漏洞連結
- [ ] 包含漏洞描述
- [ ] 有「危險寫法」範例
- [ ] 有「安全寫法」範例
- [ ] 解釋為什麼不安全
- [ ] 解釋如何修復
- [ ] 提供學習重點

## 🔎 審查流程

### 審查標準

所有 PR 都會被審查:

1. **正確性** - 資訊是否正確
2. **完整性** - 是否包含必要資訊
3. **清晰度** - 是否容易理解
4. **一致性** - 是否符合現有風格
5. **安全性** - 建議是否安全

### 審查時間

- 小改動(文字修正): 1-2 天
- 中等改動(新增範例): 3-5 天
- 大改動(新章節): 1 週

### 回應審查意見

如果審查者要求修改:

```bash
# 在你的分支上進行修改
git add .
git commit -m "fix: 根據審查意見修改"
git push origin feature/your-feature-name
```

PR 會自動更新。

## 📚 資源

### WordPress 開發資源
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WordPress Security Handbook](https://developer.wordpress.org/apis/security/)

### 安全資源
- [OWASP](https://owasp.org/)
- [WPScan](https://wpscan.com/)
- [Patchstack](https://patchstack.com/)

### Markdown
- [GitHub Flavored Markdown](https://guides.github.com/features/mastering-markdown/)
- [Markdown Guide](https://www.markdownguide.org/)

## ❓ 問題?

如果有任何問題:

- 📧 開啟 [Discussion](https://github.com/your-username/wordpress-plugin-security-skill/discussions)
- 💬 在 [Issues](https://github.com/your-username/wordpress-plugin-security-skill/issues) 中提問
- 📖 查看 [README.md](README.md)

---

再次感謝你的貢獻! 🙏

每一個貢獻都讓 WordPress 生態系統更安全! 🔐
