# Parking The Bus (BMI-ADAR) - 學習筆記

## 專案哲學：主動防禦 (Active Defense)
大多數資安外掛都是「被動」的，就像在球門前擺路障 (Firewall)。但 **Parking The Bus** 採取的是「逆向思維 (Invert, Always Invert)」：
> 我們不僅僅擋住攻擊者，我們還要讓他們**付出代價**。

如何付出代價？通過自動化向攻擊者的上游 (ISP) 進行 **Abuse Reporting (濫用舉報)**，迫使 ISP 封鎖該 IP。

## 技術架構 (Phase 1)
### 1. 資料庫設計 (`wp_bmi_abuse_reports`)
我們不依賴外部 SaaS 記錄，而是建立自己的「戰情資料庫」。
- `source_ip`: 攻擊者 IP
- `status`: 0 (Pending) -> 1 (Sent/Copied)
- `evidence_blob`: 保存攻擊當下的證據 (Log)，這是舉報成功的關鍵。

### 2. 威脅引擎 (Threat Engine)
目前監聽 `wp_login_failed`。當有人暴力破解時，我們不做無謂的封鎖 (那樣會拖慢網站)，而是：
1. **記錄 (Log)**: 將 IP 和證據寫入資料庫。
2. **查詢 (Lookup)**: (待實作) 問 AbuseIPDB 這個 IP 是誰家的 (ASN)。
3. **反擊 (Actions)**: (待實作) 發送 Email 或準備 AWS 舉報單。

## 未來展望
接下來我們會實作最酷的 **「雙模式反擊引擎」**：
- 對付一般 ISP：全自動 Email 發送。
- 對付 AWS/GCP：半自動剪貼簿輔助 (讓使用者也能輕鬆舉報)。
