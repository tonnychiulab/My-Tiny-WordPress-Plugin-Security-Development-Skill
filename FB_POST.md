# 🚀 【開源發布】WordPress 外掛開發者的安全護身符：My Tiny WordPress Plugin Security Skill

哈囉大家！如果你跟我一樣正在學習開發 WordPress 外掛，或者是常常請 AI 幫忙寫 code，這篇一定要存起來！🔖

我們都知道，寫出功能是一回事，寫出「安全」的功能又是另一回事。SQL Injection、XSS、CSRF...這些資安名詞聽起來很嚇人，但忽略它們的代價更可怕（網站被駭、資料外洩 😱）。

為了讓自己（還有我的 AI 助手）能寫出更安全的程式碼，我整理了一套 **「WordPress 外掛安全開發技能樹」**，現在正式開源分享給大家！

👉 **GitHub 傳送門：[連結請貼這裡]**

---

### 📦 這個專案裡有什麼？

這個專案不只是一份文件，它是一套完整的「防禦體系」：

1.  **📜 SKILL.md (核心秘笈)**
    這是一份給 AI (Cursor, Windsurf, Copilot) 看的「提示詞規則書」。
    只要把這份文件餵給 AI，它寫出來的程式碼就會自動具備：
    *   ✅ SQL Injection 防護 (`$wpdb->prepare`)
    *   ✅ XSS 攻擊防禦 (Escaping)
    *   ✅ CSRF 跨站偽造請求防護 (Nonces)
    *   ✅ 權限控管檢查 (`current_user_can`)
    不用再擔心 AI 寫出有漏洞的程式碼了！

2.  **🎓 LEARN.md (新手村指南)**
    覺得資安太難懂？我用「城堡防禦」的比喻，把生硬的技術名詞變成了有趣的故事。
    *   **掃描機 (Prepare)**：防止木馬屠城
    *   **消毒水 (Escape)**：防止亂塗鴉
    *   **通關密語 (Nonce)**：防止偽造聖旨
    就算完全不懂程式，也能看懂我們在防什麼！

3.  **⚖️ 天堂與地獄：實戰示範外掛**
    為了讓大家更有感，我寫了兩個功能一模一樣，安全性卻天差地遠的外掛：
    *   💀 **Doomed Diary (註定毀滅的日記)**：
        集滿了所有新手常犯的錯誤（SQLi, XSS, CSRF 全都有），堪稱「最危險的外掛」。
    *   🛡️ **Guardian Diary (守護者日記)**：
        這是正確示範！展示了如何用最標準的 WordPress 函式庫來防禦上述所有攻擊。
    
    **(強烈建議下載下來，對照著看程式碼，你會有種「原來是這樣！」的頓悟感)**

---

### 🤝 為什麼要開源？

資安不是一個人的事，而是整個 WordPress 社群的責任。這份技能樹是我目前的學習筆記，但我相信它還可以更完善。

歡迎大家來 GitHub 幫我按個星星 ⭐，如果你發現了新的漏洞案例或防禦技巧，也非常歡迎發 PR 或 Issue 一起交流！

讓我們一起把 WordPress 生態圈變得更安全吧！💪

#WordPress #PluginDevelopment #Security #OpenSource #GitHub #AIcoding #Cursor #Windsurf
