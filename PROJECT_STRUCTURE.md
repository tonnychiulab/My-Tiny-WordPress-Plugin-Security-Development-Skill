# 專案結構說明

本文件說明整個專案的檔案組織結構。

## 📁 目錄結構

```
wordpress-plugin-security-skill/
│
├── .github/                          # GitHub 配置
│   ├── ISSUE_TEMPLATE/              # Issue 模板
│   │   ├── bug_report.md           # Bug 回報模板
│   │   ├── feature_request.md      # 功能建議模板
│   │   └── security_case.md        # 安全案例模板
│   └── PULL_REQUEST_TEMPLATE.md    # PR 模板
│
├── SKILL.md                         # 主要技能文件 ⭐
├── WordPress-Security-Quick-Reference.md  # 快速參考指南
├── How-to-Use-This-Skill.md        # 使用說明
│
├── README.md                        # 專案說明
├── CONTRIBUTING.md                  # 貢獻指南
├── LICENSE                          # MIT 授權
├── .gitignore                       # Git 忽略檔案
└── PROJECT_STRUCTURE.md            # 本文件
```

## 📄 檔案說明

### 核心文件

#### SKILL.md
**主要技能文件** - 完整的 WordPress 外掛安全開發指南

**內容結構:**
```
1. 概述與資料來源
2. 核心安全原則
3. SQL Injection 防護
4. Cross-Site Scripting (XSS) 防護
5. Cross-Site Request Forgery (CSRF) 防護
6. Broken Access Control (權限控制)
7. Sensitive Data Exposure (敏感資料洩露)
8. File Upload 安全
9. 資料淨化函數總覽
10. 資料驗證實踐
11. 安全開發檢查清單
12. 常見漏洞案例參考
13. WPScan API 使用方法
14. 開發工具與資源
15. 持續安全實踐
16. AI 程式開發時的使用建議
```

**特色:**
- ✅ 每個安全問題都有「危險寫法」vs「安全寫法」對比
- ✅ 基於 WPScan 和 Patchstack 的真實案例
- ✅ 完整的程式碼範例
- ✅ 安全檢查清單

**適用對象:**
- WordPress 外掛開發者
- 安全審查人員
- 技術主管
- 想學習 WordPress 安全的開發者

---

#### WordPress-Security-Quick-Reference.md
**快速參考指南** - 安全函數速查表

**內容:**
- 輸入淨化函數表
- 輸出轉義函數表
- SQL 安全函數
- CSRF 防護 (Nonces)
- 權限檢查
- 驗證函數
- 快速決策樹
- 常見錯誤範例

**使用時機:**
- 快速查找函數
- Code Review 時參考
- 不確定該用哪個函數時

---

#### How-to-Use-This-Skill.md
**使用說明** - 詳細的使用情境與範例

**內容:**
- 各種使用場景
- 實際 Prompt 範例
- 與 AI 協作方法
- 團隊協作使用
- 持續學習建議

**適用場景:**
- 第一次使用這個 skill
- 想了解如何與 AI 協作
- 建立團隊開發規範
- 程式碼審查

---

### 專案文件

#### README.md
**專案說明** - GitHub 首頁展示文件

**內容:**
- 專案簡介
- 為什麼需要這個 skill
- 特色功能
- 快速開始
- 使用方式
- 貢獻指南連結

**目標讀者:**
- 訪問 GitHub repo 的開發者
- 想了解專案的人
- 想貢獻的人

---

#### CONTRIBUTING.md
**貢獻指南** - 如何為專案做出貢獻

**內容:**
- 行為準則
- 如何貢獻
- 回報問題流程
- 提交變更流程
- 撰寫規範
- 新增安全案例指南
- 審查流程

**目標讀者:**
- 想貢獻的開發者
- PR 提交者
- 案例貢獻者

---

#### LICENSE
**MIT 授權** - 開源授權條款

**重點:**
- 允許商業使用
- 允許修改
- 允許分發
- 需保留版權聲明

---

#### .gitignore
**Git 忽略檔案** - 指定不要追蹤的檔案

**忽略內容:**
- OS 檔案 (.DS_Store, Thumbs.db)
- IDE 設定 (.vscode, .idea)
- Node modules
- 臨時檔案

---

### GitHub 模板

#### .github/ISSUE_TEMPLATE/bug_report.md
**Bug 回報模板**

**欄位:**
- 問題描述
- 問題位置
- 發現的問題
- 建議的修正
- 參考資料

---

#### .github/ISSUE_TEMPLATE/feature_request.md
**功能建議模板**

**欄位:**
- 功能描述
- 使用場景
- 建議內容
- 替代方案
- 參考資料

---

#### .github/ISSUE_TEMPLATE/security_case.md
**安全案例模板**

**欄位:**
- 漏洞類型
- CVE 編號
- 來源連結
- 影響範圍
- 漏洞描述
- 危險程式碼
- 安全程式碼
- 學習重點

---

#### .github/PULL_REQUEST_TEMPLATE.md
**Pull Request 模板**

**欄位:**
- 變更說明
- 變更類型
- 影響的檔案
- 檢查清單
- 相關 Issue
- 測試說明

---

## 🔄 文件關係

```
README.md
    ↓ (引導到)
SKILL.md ←→ WordPress-Security-Quick-Reference.md
    ↓                           ↑
How-to-Use-This-Skill.md ------┘
    ↓
CONTRIBUTING.md
```

**閱讀順序建議:**

1. **新手:**
   ```
   README.md → How-to-Use-This-Skill.md → SKILL.md
   ```

2. **開發者:**
   ```
   README.md → SKILL.md ⇄ WordPress-Security-Quick-Reference.md
   ```

3. **貢獻者:**
   ```
   README.md → CONTRIBUTING.md → SKILL.md
   ```

4. **快速查詢:**
   ```
   WordPress-Security-Quick-Reference.md
   ```

---

## 📝 維護指南

### 更新 SKILL.md 時

需要同步更新:
- [ ] `README.md` - 如果有新增主要章節
- [ ] `WordPress-Security-Quick-Reference.md` - 如果有新函數
- [ ] `How-to-Use-This-Skill.md` - 如果有新的使用方式

### 新增安全案例時

1. 在 `SKILL.md` 對應章節新增
2. 確認 `WordPress-Security-Quick-Reference.md` 是否需要更新
3. 考慮在 `How-to-Use-This-Skill.md` 新增使用範例

### 版本發布時

1. 更新 `SKILL.md` 版本號
2. 更新 `README.md` 更新日期
3. 建立 GitHub Release
4. 標記 Git Tag

---

## 🎯 未來計劃

### 可能新增的文件

- [ ] `CHANGELOG.md` - 變更日誌
- [ ] `FAQ.md` - 常見問題
- [ ] `ROADMAP.md` - 發展路線圖
- [ ] `CODE_OF_CONDUCT.md` - 行為準則
- [ ] `SECURITY.md` - 安全政策
- [ ] 語言版本
  - [ ] `README_EN.md` - 英文版
  - [ ] `SKILL_EN.md` - 英文版技能文件

### 可能新增的目錄

- [ ] `examples/` - 完整範例專案
- [ ] `tools/` - 輔助工具腳本
- [ ] `tests/` - 測試檔案
- [ ] `docs/` - 額外文件
- [ ] `translations/` - 翻譯文件

---

## 📊 檔案大小參考

| 檔案 | 大小 | 主要內容 |
|------|------|----------|
| SKILL.md | ~50KB | 完整安全指南 |
| WordPress-Security-Quick-Reference.md | ~15KB | 函數速查表 |
| How-to-Use-This-Skill.md | ~10KB | 使用說明 |
| README.md | ~12KB | 專案說明 |
| CONTRIBUTING.md | ~8KB | 貢獻指南 |

---

## 🔗 外部連結

專案中引用的主要資源:

- **WPScan**: https://wpscan.com/
- **Patchstack**: https://patchstack.com/
- **WordPress Plugin Handbook**: https://developer.wordpress.org/plugins/
- **OWASP**: https://owasp.org/

---

**最後更新**: 2025-02-08
