# 程式碼風格

- 這是一個 PHP 專案，所以所有程式碼都應該用 PHP 寫。
- 如果我沒有特別指定 PHP 框架，一律使用標準的 PHP（不使用框架）。
- 我若沒有特別指定，就不要套用任何 PHP 框架。
- Claude 在產生程式碼時一律要相容於 PHP7 + PHP8。
- Claude 在使用 curl 相關操作時一律使用 PHP Guzzle。
- **Claude 必須在每次提供程式碼時，都要附上使用範例和說明**，展示如何實際運用該程式碼
- **總是使用高度語義化和描述性的類別、函數和參數名稱，即使可能會比較冗長**
- **所有測試檔案都要依照 Test 目錄中的模式撰寫，檔案名稱統一為 `SkeltonModule_Comprehensive_Test_Executor`**
- **Claude 在撰寫測試檔案和範例程式碼時，一律要依照 `SkeltonModule_Comprehensive_Test_Executor` 的模式和架構**
- **Claude 在產生測試和使用範例時，一律要使用具體的 Module 名稱，而不是使用泛用的佔位符**
- **Claude Code 在撰寫所有的測試檔案和範例使用檔案時，都要用具體的 Module 名稱做適當的命名**
- **一律在 Test 資料夾中生成符合 `SkeltonModule_Comprehensive_Test_Executor` 模式的範例使用檔案，並命名為 `usage_example.php`**
- **優先使用可串接的 getter 和 setter 模式來設定所有必要和可選的參數及設定，所有 setter 方法都要回傳 $this 以支援鏈式調用**
- **Claude Code 一律要撰寫 README.md 檔案來展示模組的「如何使用」範例**
- **Claude Code 要建立一個集中式的設定管理機制，用來設定所有必要和可選的設定參數，例如 API key、服務名稱和其他參數，避免分散在各個類別中**
- **Claude Code 在撰寫測試和範例使用檔案時，一律要使用以下模式做為初始設定：**
  ```php
  $currentWorkingDirectory = getcwd();
  $_SERVER['DOCUMENT_ROOT'] = $currentWorkingDirectory;
  ```
- **Claude Code 在載入 `vendor/autoload.php` 時，一律要使用 `$_SERVER['DOCUMENT_ROOT']` 路徑：**
  ```php
  require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
  ```
- Module 結構依照以下的基礎架構，但根據實際需求彈性調整：
  ```
  ./{ModuleName}
  └── v1
      └── Admin
          ├── Controller (可選：需要 API 端點時)
          │   └── {ModuleName}_Controller.php
          ├── DAO (可選：需要資料庫操作時)
          │   └── {ModuleName}_DAO.php
          ├── Model (可選：需要資料模型時)
          │   ├── {ModuleName}_Model.php
          │   └── {ModuleName}_Model_Iterator.php
          ├── Service (必要：業務邏輯層)
          │   └── {ModuleName}_Service.php
          ├── Test (可選：測試檔案)
          │   ├── SkeltonModule_Comprehensive_Test_Executor.php
          │   └── usage_example.php
          └── README.md
  ```
- **Service 層是必要的**：所有 Module 都應該至少包含 Service 類別來處理業務邏輯
- **RESTful API 服務專用規則**：如果需求是建立使用 RESTful API 的服務，則只需要建立 Service 層，**不需要建立 Model、Model Iterator、DAO 和 Controller**
- **其他層視需求新增**：
    - Controller：當需要提供 API 端點時才建立
    - DAO：當需要資料庫操作時才建立
    - Model：當需要資料模型封裝時才建立
    - Iterator：當需要處理集合資料時才建立
    - Test：當需要單元測試時才建立
- **Service Pattern 原則**：Service 類別應該專注於業務邏輯，保持方法職責單一
- **模組開發參考模板**：使用 `@module_skelton/Module_Skelton/` 作為所有新模組的開發模板
    - 複製整個 `Module_Skelton` 目錄結構
    - 將所有檔案名稱中的 `SkeltonModule` 替換為實際的模組名稱
    - 將程式碼中的 `SkeltonModule` 替換為實際的模組名稱
    - 將資料庫表格名稱 `skelton_module` 替換為實際的表格名稱
    - 確保所有命名空間、類別名稱、檔案引用都正確更新
- 如果我沒有特別指定 CSS 框架，一律使用原生 CSS。
- CSS 一律在所有 class 和 ID 前面加上 `deus-` 前綴，避免與其他樣式衝突。
- CSS 應該用模組化的方式撰寫，方便覆寫和擴充。
- CSS 應該用容易閱讀和維護的方式組織，加上清楚的註解和結構。
- CSS 應該用良好結構的命名空間撰寫，避免與其他樣式衝突。
- CSS 應該具備響應式設計，在桌面和行動裝置上都能正常運作。
- 如果我沒有特別指定 HTML，一律使用標準 HTML5。
- 確保你用適當的 PHP 命名空間和類別封裝來隔離程式碼，避免與全域範圍衝突。
- **特別重要：每一次的程式修改都要 100% 避免 breaking changes，絕對不能破壞現有功能。**
- 確保你寫的任何程式碼都是正式環境可用的，並且可以直接運作。

## 資料庫操作規範

- 所有資料庫操作都必須透過 DAO 層執行，不允許在 Controller 或 Service 中直接寫 SQL
- 使用 PDO 進行資料庫連接，確保預處理語句防止 SQL 注入
- 所有 SQL 查詢都要使用參數綁定，禁止字串拼接
- 資料庫欄位命名使用 snake_case（如：user_name, created_at）
- 所有資料表都必須包含：uuid（主鍵）、status、since、lastupdate 欄位
- 軟刪除一律使用 status 欄位，不要物理刪除資料

## CLI 指令規範

- 所有 CLI 指令都必須放在專案根目錄的 `cli` 資料夾中
- CLI 指令檔案命名使用小寫字母和底線（如：data_migration.php）
- 每個 CLI 指令檔案都必須包含清楚的說明和使用範例
- 使用 PHP 的 `getopt` 函數解析命令列參數
- 所有 CLI 指令都必須有錯誤處理和回報機制
- 使用者可以用 `php cli/command_name.php --help` 查看指令說明