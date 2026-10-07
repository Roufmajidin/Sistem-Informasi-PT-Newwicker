import "./taskpane.css";

const API_BASE_URL = "http://127.0.0.1:8000";
const API_PATH = "/api/qc/laporan/add-in";

const SHEET_NAME = "QC Report";
const TABLE_NAME = "QCReportTable";

let cachedData = [];


// ============================================================
// OFFICE READY
// ============================================================

Office.onReady(async (info) => {
  if (info.host !== Office.HostType.Excel) {
    setStatus(
      "Add-in ini hanya untuk Microsoft Excel.",
      "error"
    );
    return;
  }

  bindEvents();
  setDefaultDates();

  await refreshWorksheetInfo();
  await loadInspectors();
});


// ============================================================
// EVENT
// ============================================================

function bindEvents() {
  document
    .getElementById("loadBtn")
    ?.addEventListener("click", loadQcData);

  document
    .getElementById("clearBtn")
    ?.addEventListener("click", clearFilters);

  document
    .getElementById("createSheetBtn")
    ?.addEventListener("click", ensureQcSheet);
}


// ============================================================
// DEFAULT DATE
// ============================================================

function setDefaultDates() {
  const today = new Date();

  const pad = (n) =>
    String(n).padStart(2, "0");

  const dateTo =
    `${today.getFullYear()}-` +
    `${pad(today.getMonth() + 1)}-` +
    `${pad(today.getDate())}`;

  const dateToElement =
    document.getElementById("dateTo");

  if (dateToElement) {
    dateToElement.value = dateTo;
  }

  const first = new Date(
    today.getFullYear(),
    today.getMonth(),
    1
  );

  const dateFrom =
    `${first.getFullYear()}-` +
    `${pad(first.getMonth() + 1)}-` +
    `${pad(first.getDate())}`;

  const dateFromElement =
    document.getElementById("dateFrom");

  if (dateFromElement) {
    dateFromElement.value = dateFrom;
  }
}


// ============================================================
// API FETCH
// ============================================================

async function apiFetch(url) {
  const response = await fetch(url, {
    method: "GET",
    headers: {
      Accept: "application/json"
    }
  });

  if (!response.ok) {
    const text = await response.text();

    throw new Error(
      `HTTP ${response.status}: ${text.substring(0, 300)}`
    );
  }

  return response.json();
}


// ============================================================
// LOAD INSPECTORS
// ============================================================

async function loadInspectors() {
  try {
    const url = new URL(
      API_PATH,
      API_BASE_URL
    );

    const result =
      await apiFetch(url.toString());

    console.log(
      "[QC ADD-IN] Inspector API:",
      result
    );

    const inspectors =
      extractInspectors(result);

    const select =
      document.getElementById("inspector");

    if (!select) {
      return;
    }

    select.innerHTML =
      '<option value="">Semua Inspector</option>';

    inspectors.forEach((item) => {

      const id =
        item?.id ??
        item?.user_id ??
        "";

      const name =
        item?.name ??
        item?.nama_lengkap ??
        item?.nama ??
        item?.person ??
        `Inspector ${id}`;

      const option =
        document.createElement("option");

      option.value =
        String(id);

      option.textContent =
        String(name);

      select.appendChild(option);
    });

  } catch (error) {

    console.warn(
      "[QC ADD-IN] Inspector loading skipped:",
      error
    );
  }
}


// ============================================================
// EXTRACT INSPECTORS
// ============================================================

function extractInspectors(result) {

  if (Array.isArray(result?.qcs)) {
    return result.qcs;
  }

  if (
    Array.isArray(
      result?.data?.qcs
    )
  ) {
    return result.data.qcs;
  }

  if (
    Array.isArray(
      result?.inspectors
    )
  ) {
    return result.inspectors;
  }

  return [];
}


// ============================================================
// LOAD QC DATA
// ============================================================

async function loadQcData() {

  const button =
    document.getElementById("loadBtn");

  const inspector =
    document.getElementById("inspector")
      ?.value ?? "";

  const from =
    document.getElementById("dateFrom")
      ?.value ?? "";

  const to =
    document.getElementById("dateTo")
      ?.value ?? "";


  // ----------------------------------------------------------
  // VALIDASI TANGGAL
  // ----------------------------------------------------------

  if (from && to && from > to) {

    setStatus(
      "Tanggal From tidak boleh lebih besar dari To.",
      "error"
    );

    return;
  }


  // ----------------------------------------------------------
  // BUTTON LOADING
  // ----------------------------------------------------------

  if (button) {

    button.disabled = true;
    button.textContent = "Loading...";
  }


  setStatus(
    "Mengambil data dari Laravel...",
    "loading"
  );


  try {

    const url =
      new URL(
        API_PATH,
        API_BASE_URL
      );


    // --------------------------------------------------------
    // FILTER INSPECTOR
    // --------------------------------------------------------

    if (inspector) {

      url.searchParams.set(
        "inspector",
        inspector
      );
    }


    // --------------------------------------------------------
    // FILTER FROM
    // --------------------------------------------------------

    if (from) {

      url.searchParams.set(
        "from",
        from
      );
    }


    // --------------------------------------------------------
    // FILTER TO
    // --------------------------------------------------------

    if (to) {

      url.searchParams.set(
        "to",
        to
      );
    }


    console.log(
      "[QC ADD-IN] Request URL:",
      url.toString()
    );


    // --------------------------------------------------------
    // API
    // --------------------------------------------------------

    const result =
      await apiFetch(
        url.toString()
      );


    console.log(
      "[QC ADD-IN] RAW API RESPONSE:",
      result
    );


    // --------------------------------------------------------
    // NORMALIZE
    // --------------------------------------------------------

    const rows =
      normalizeQcRows(result);


    console.log(
      "[QC ADD-IN] NORMALIZED ROWS:",
      rows
    );


    console.table(
      rows.map((row) => ({
        no: row.no,
        po: row.po,
        name_items: row.name_items,
        buyer: row.buyer,
        no_spk: row.no_spk,
        sub_name: row.sub_name,
        person: row.person,
        total_inspected: row.total_inspected,
        pass: row.pass,
        reject: row.reject
      }))
    );


    // --------------------------------------------------------
    // DEBUG SUB NAME
    // --------------------------------------------------------

    console.log(
      "[QC ADD-IN] SUB NAME CHECK:",
      rows.map((row) => ({
        no_spk: row.no_spk,
        sub_name: row.sub_name
      }))
    );


    cachedData = rows;


    // --------------------------------------------------------
    // WRITE EXCEL
    // --------------------------------------------------------

    await writeToExcel(rows);


    // --------------------------------------------------------
    // STATUS
    // --------------------------------------------------------

    setStatus(
      `${rows.length} data QC berhasil dimuat ke Excel.`,
      "success"
    );


    const rowCount =
      document.getElementById(
        "rowCount"
      );

    if (rowCount) {

      rowCount.textContent =
        `${rows.length} rows`;
    }


  } catch (error) {

    console.error(
      "[QC ADD-IN] Load QC Data Error:",
      error
    );

    setStatus(
      `Gagal: ${error.message}`,
      "error"
    );


  } finally {

    if (button) {

      button.disabled = false;
      button.textContent =
        "Load QC Data";
    }
  }
}


// ============================================================
// NORMALIZE QC ROWS
// ============================================================

function normalizeQcRows(result) {

  let source = [];


  // ----------------------------------------------------------
  // RESPONSE:
  //
  // {
  //     data: [...]
  // }
  // ----------------------------------------------------------

  if (
    Array.isArray(
      result?.data
    )
  ) {

    source =
      result.data;


  // ----------------------------------------------------------
  // RESPONSE:
  //
  // {
  //     inspection: [...]
  // }
  // ----------------------------------------------------------

  } else if (
    Array.isArray(
      result?.inspection
    )
  ) {

    source =
      result.inspection;


  // ----------------------------------------------------------
  // RESPONSE LAMA:
  //
  // {
  //     data: {
  //         inspection: [...]
  //     }
  // }
  // ----------------------------------------------------------

  } else if (
    Array.isArray(
      result?.data?.inspection
    )
  ) {

    source =
      result.data.inspection;


  // ----------------------------------------------------------
  // RESPONSE:
  //
  // {
  //     rows: [...]
  // }
  // ----------------------------------------------------------

  } else if (
    Array.isArray(
      result?.rows
    )
  ) {

    source =
      result.rows;
  }


  console.log(
    "[QC ADD-IN] SOURCE DATA:",
    source
  );


  // ----------------------------------------------------------
  // MAP
  // ----------------------------------------------------------

  return source.map(
    (item, index) => {

      const row = {

        // ----------------------------------------------------
        // #
        // ----------------------------------------------------

        no:
          index + 1,


        // ----------------------------------------------------
        // TANGGAL JAM
        // ----------------------------------------------------

        tanggal:
          pick(
            item,
            [
              "tanggal",
              "tanggal_inspect",
              "date"
            ]
          ),


        // ----------------------------------------------------
        // PO
        // ----------------------------------------------------

        po:
          pick(
            item,
            [
              "po",
              "order_no",
              "po_no"
            ]
          ),


        // ----------------------------------------------------
        // NAME ITEMS
        // ----------------------------------------------------

        name_items:
          pick(
            item,
            [
              "name_items",
              "description",
              "desc",
              "nama_barang"
            ]
          ),


        // ----------------------------------------------------
        // BUYER
        // ----------------------------------------------------

        buyer:
          pick(
            item,
            [
              "buyer",
              "company_name",
              "customer"
            ]
          ),


        // ----------------------------------------------------
        // NO SPK
        // ----------------------------------------------------

        no_spk:
          pick(
            item,
            [
              "no_spk",
              "spk",
              "spk_no"
            ]
          ),


        // ----------------------------------------------------
        // SUB NAME
        // ----------------------------------------------------

        sub_name:
          pick(
            item,
            [
              "sub_name",
              "sub",
              "supplier"
            ]
          ),


        // ----------------------------------------------------
        // PERSON
        // ----------------------------------------------------

        person:
          pick(
            item,
            [
              "person",
              "qc",
              "inspector",
              "user_name",
              "name",
              "nama_lengkap",
              "nama"
            ]
          ),


        // ----------------------------------------------------
        // TOTAL INSPECTED
        // ----------------------------------------------------

        total_inspected:
          pick(
            item,
            [
              "total_inspected",
              "inspect",
              "jumlah_inspect",
              "qty_inspect"
            ]
          ),


        // ----------------------------------------------------
        // PASS
        // ----------------------------------------------------

        pass:
          pick(
            item,
            [
              "pass",
              "passed"
            ]
          ),


        // ----------------------------------------------------
        // REJECT
        // ----------------------------------------------------

        reject:
          pick(
            item,
            [
              "reject",
              "rejected"
            ]
          )
      };


      console.log(
        `[QC ADD-IN] Row ${index + 1}:`,
        row
      );


      return row;
    }
  );
}


// ============================================================
// PICK
// ============================================================

function pick(obj, keys) {

  if (
    !obj ||
    typeof obj !== "object"
  ) {

    return "";
  }


  for (const key of keys) {

    if (
      obj[key] !== undefined &&
      obj[key] !== null
    ) {

      const value =
        normalizeValue(
          obj[key]
        );


      if (
        value !== ""
      ) {

        return value;
      }
    }
  }


  return "";
}


// ============================================================
// NORMALIZE VALUE
// ============================================================

function normalizeValue(value) {

  // ----------------------------------------------------------
  // NULL / UNDEFINED
  // ----------------------------------------------------------

  if (
    value === null ||
    value === undefined
  ) {

    return "";
  }


  // ----------------------------------------------------------
  // STRING
  // ----------------------------------------------------------

  if (
    typeof value === "string"
  ) {

    return value;
  }


  // ----------------------------------------------------------
  // NUMBER
  // ----------------------------------------------------------

  if (
    typeof value === "number"
  ) {

    return Number.isFinite(value)
      ? value
      : "";
  }


  // ----------------------------------------------------------
  // BOOLEAN
  // ----------------------------------------------------------

  if (
    typeof value === "boolean"
  ) {

    return value;
  }


  // ----------------------------------------------------------
  // ARRAY
  // ----------------------------------------------------------

  if (
    Array.isArray(value)
  ) {

    return value
      .map(
        (item) =>
          normalizeValue(item)
      )
      .filter(
        (item) =>
          item !== ""
      )
      .join(", ");
  }


  // ----------------------------------------------------------
  // OBJECT
  // ----------------------------------------------------------

  if (
    typeof value === "object"
  ) {

    /*
    |--------------------------------------------------------------------------
    | PERSON
    |--------------------------------------------------------------------------
    */

    if (
      value.person !== undefined
    ) {

      return normalizeValue(
        value.person
      );
    }


    /*
    |--------------------------------------------------------------------------
    | SUB NAME
    |--------------------------------------------------------------------------
    */

    if (
      value.sub_name !== undefined
    ) {

      return normalizeValue(
        value.sub_name
      );
    }


    /*
    |--------------------------------------------------------------------------
    | SUPPLIER
    |--------------------------------------------------------------------------
    */

    if (
      value.supplier !== undefined
    ) {

      return normalizeValue(
        value.supplier
      );
    }


    /*
    |--------------------------------------------------------------------------
    | SUB
    |--------------------------------------------------------------------------
    */

    if (
      value.sub !== undefined
    ) {

      return normalizeValue(
        value.sub
      );
    }


    /*
    |--------------------------------------------------------------------------
    | KATEGORI
    |--------------------------------------------------------------------------
    */

    if (
      value.kategori !== undefined
    ) {

      return normalizeValue(
        value.kategori
      );
    }


    /*
    |--------------------------------------------------------------------------
    | CATEGORY
    |--------------------------------------------------------------------------
    */

    if (
      value.category !== undefined
    ) {

      return normalizeValue(
        value.category
      );
    }


    /*
    |--------------------------------------------------------------------------
    | NAME
    |--------------------------------------------------------------------------
    */

    if (
      value.name !== undefined
    ) {

      return normalizeValue(
        value.name
      );
    }


    /*
    |--------------------------------------------------------------------------
    | NAMA LENGKAP
    |--------------------------------------------------------------------------
    */

    if (
      value.nama_lengkap !== undefined
    ) {

      return normalizeValue(
        value.nama_lengkap
      );
    }


    /*
    |--------------------------------------------------------------------------
    | NAMA
    |--------------------------------------------------------------------------
    */

    if (
      value.nama !== undefined
    ) {

      return normalizeValue(
        value.nama
      );
    }


    /*
    |--------------------------------------------------------------------------
    | DESCRIPTION
    |--------------------------------------------------------------------------
    */

    if (
      value.description !== undefined
    ) {

      return normalizeValue(
        value.description
      );
    }


    /*
    |--------------------------------------------------------------------------
    | NO SPK
    |--------------------------------------------------------------------------
    */

    if (
      value.no_spk !== undefined
    ) {

      return normalizeValue(
        value.no_spk
      );
    }


    /*
    |--------------------------------------------------------------------------
    | ORDER NO
    |--------------------------------------------------------------------------
    */

    if (
      value.order_no !== undefined
    ) {

      return normalizeValue(
        value.order_no
      );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPANY NAME
    |--------------------------------------------------------------------------
    */

    if (
      value.company_name !== undefined
    ) {

      return normalizeValue(
        value.company_name
      );
    }


    /*
    |--------------------------------------------------------------------------
    | USER NAME
    |--------------------------------------------------------------------------
    */

    if (
      value.user_name !== undefined
    ) {

      return normalizeValue(
        value.user_name
      );
    }


    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    if (
      value.data !== undefined
    ) {

      return normalizeValue(
        value.data
      );
    }


    return "";
  }


  // ----------------------------------------------------------
  // FALLBACK
  // ----------------------------------------------------------

  return String(value);
}


// ============================================================
// WRITE TO EXCEL
// ============================================================

async function writeToExcel(rows) {

  await Excel.run(
    async (context) => {

      // ------------------------------------------------------
      // SHEET
      // ------------------------------------------------------

      const sheet =
        await ensureQcSheet(
          context
        );


      // ------------------------------------------------------
      // HEADER
      // ------------------------------------------------------

      const headers = [

        "#",

        "TANGGAL JAM",

        "PO",

        "NAME ITEMS",

        "BUYER",

        "NO. SPK",

        "SUB NAME",

        "PERSON",

        "TOTAL INSPECTED",

        "PASS",

        "REJECT"

      ];


      // ------------------------------------------------------
      // DATA ROWS
      // ------------------------------------------------------

      const dataRows =
        rows.map(
          (row) => [

            normalizeValue(
              row.no
            ),

            normalizeValue(
              row.tanggal
            ),

            normalizeValue(
              row.po
            ),

            normalizeValue(
              row.name_items
            ),

            normalizeValue(
              row.buyer
            ),

            normalizeValue(
              row.no_spk
            ),

            // SUB NAME
            normalizeValue(
              row.sub_name
            ),

            // PERSON
            normalizeValue(
              row.person
            ),

            // TOTAL INSPECTED
            normalizeValue(
              row.total_inspected
            ),

            // PASS
            normalizeValue(
              row.pass
            ),

            // REJECT
            normalizeValue(
              row.reject
            )
          ]
        );


      // ------------------------------------------------------
      // FINAL VALUES
      // ------------------------------------------------------

      const values = [
        headers,
        ...dataRows
      ];


      console.log(
        "[QC ADD-IN] EXCEL VALUES:",
        values
      );


      // ------------------------------------------------------
      // CLEAR OLD SHEET
      // ------------------------------------------------------

      const used =
        sheet.getUsedRangeOrNullObject();

      used.load(
        "address,isNullObject"
      );

      await context.sync();


      if (
        !used.isNullObject
      ) {

        used.clear(
          Excel.ClearApplyTo.all
        );
      }


      // ------------------------------------------------------
      // DELETE OLD TABLE
      // ------------------------------------------------------

      const tables =
        sheet.tables;

      tables.load(
        "items/name"
      );

      await context.sync();


      if (
        tables.items.length > 0
      ) {

        tables.items.forEach(
          (table) => {

            table.delete();
          }
        );

        await context.sync();
      }


      // ------------------------------------------------------
      // RANGE
      // ------------------------------------------------------

      const totalRows =
        Math.max(
          values.length,
          1
        );


      const totalColumns =
        headers.length;


      const range =
        sheet.getRangeByIndexes(
          0,
          0,
          totalRows,
          totalColumns
        );


      // ------------------------------------------------------
      // WRITE
      // ------------------------------------------------------

      range.values =
        values.length
          ? values
          : [headers];


      // ------------------------------------------------------
      // HEADER FORMAT
      // ------------------------------------------------------

      const header =
        sheet.getRangeByIndexes(
          0,
          0,
          1,
          headers.length
        );


      header.format.font.bold =
        true;

      header.format.wrapText =
        true;

      header.format.verticalAlignment =
        "Center";


      // ------------------------------------------------------
      // GENERAL FORMAT
      // ------------------------------------------------------

      range.format.verticalAlignment =
        "Center";

      range.format.wrapText =
        true;


      // ------------------------------------------------------
      // COLUMN WIDTH
      // ------------------------------------------------------

      sheet
        .getRange("A:A")
        .format.columnWidth = 35;


      sheet
        .getRange("B:B")
        .format.columnWidth = 105;


      sheet
        .getRange("C:C")
        .format.columnWidth = 90;


      sheet
        .getRange("D:D")
        .format.columnWidth = 250;


      sheet
        .getRange("E:E")
        .format.columnWidth = 130;


      sheet
        .getRange("F:F")
        .format.columnWidth = 130;


      sheet
        .getRange("G:G")
        .format.columnWidth = 130;


      sheet
        .getRange("H:H")
        .format.columnWidth = 120;


      sheet
        .getRange("I:I")
        .format.columnWidth = 105;


      sheet
        .getRange("J:J")
        .format.columnWidth = 70;


      sheet
        .getRange("K:K")
        .format.columnWidth = 70;


      // ------------------------------------------------------
      // WRAP TEXT
      // ------------------------------------------------------

      sheet
        .getRange("B:B")
        .format.wrapText = true;


      sheet
        .getRange("D:D")
        .format.wrapText = true;


      sheet
        .getRange("G:G")
        .format.wrapText = true;


      sheet
        .getRange("H:H")
        .format.wrapText = true;


      // ------------------------------------------------------
      // ALIGNMENT
      // ------------------------------------------------------

      sheet
        .getRange("A:A")
        .format.horizontalAlignment =
        "Center";


      sheet
        .getRange("I:K")
        .format.horizontalAlignment =
        "Center";


      // ------------------------------------------------------
      // TABLE
      // ------------------------------------------------------

      const tableRange =
        sheet.getRangeByIndexes(
          0,
          0,
          totalRows,
          headers.length
        );


      const table =
        sheet.tables.add(
          tableRange,
          true
        );


      table.name =
        TABLE_NAME;


      table.style =
        "TableStyleMedium2";


      // ------------------------------------------------------
      // FREEZE HEADER
      // ------------------------------------------------------

      sheet
        .freezePanes
        .freezeRows(1);


      // ------------------------------------------------------
      // ACTIVATE SHEET
      // ------------------------------------------------------

      sheet.activate();


      // ------------------------------------------------------
      // SYNC
      // ------------------------------------------------------

      await context.sync();


      console.log(
        "[QC ADD-IN] Excel write SUCCESS"
      );
    }
  );
}


// ============================================================
// ENSURE QC SHEET
// ============================================================

async function ensureQcSheet(
  existingContext = null
) {

  // ==========================================================
  // EXISTING CONTEXT
  // ==========================================================

  if (existingContext) {

    const context =
      existingContext;


    let sheet =
      context.workbook.worksheets
        .getItemOrNullObject(
          SHEET_NAME
        );


    sheet.load(
      "name,isNullObject"
    );


    await context.sync();


    if (
      sheet.isNullObject
    ) {

      sheet =
        context.workbook.worksheets
          .add(
            SHEET_NAME
          );

      await context.sync();
    }


    return sheet;
  }


  // ==========================================================
  // NEW EXCEL CONTEXT
  // ==========================================================

  await Excel.run(
    async (context) => {

      let sheet =
        context.workbook.worksheets
          .getItemOrNullObject(
            SHEET_NAME
          );


      sheet.load(
        "name,isNullObject"
      );


      await context.sync();


      if (
        sheet.isNullObject
      ) {

        sheet =
          context.workbook.worksheets
            .add(
              SHEET_NAME
            );

        await context.sync();
      }


      sheet.activate();

      await context.sync();
    }
  );


  await refreshWorksheetInfo();

  return true;
}


// ============================================================
// REFRESH WORKSHEET INFO
// ============================================================

async function refreshWorksheetInfo() {

  try {

    await Excel.run(
      async (context) => {

        const sheet =
          context.workbook.worksheets
            .getItemOrNullObject(
              SHEET_NAME
            );


        sheet.load(
          "name,isNullObject"
        );


        await context.sync();


        const element =
          document.getElementById(
            "worksheetName"
          );


        if (element) {

          element.textContent =
            sheet.isNullObject
              ? "Sheet belum dibuat"
              : sheet.name;
        }
      }
    );

  } catch (error) {

    console.warn(
      "[QC ADD-IN] Refresh worksheet info error:",
      error
    );
  }
}


// ============================================================
// CLEAR FILTERS
// ============================================================

function clearFilters() {

  const inspector =
    document.getElementById(
      "inspector"
    );


  if (inspector) {

    inspector.value = "";
  }


  setDefaultDates();


  cachedData = [];


  const rowCount =
    document.getElementById(
      "rowCount"
    );


  if (rowCount) {

    rowCount.textContent =
      "0 rows";
  }


  setStatus(
    "Filter di-reset.",
    "neutral"
  );
}


// ============================================================
// STATUS
// ============================================================

function setStatus(
  message,
  type
) {

  const element =
    document.getElementById(
      "status"
    );


  if (!element) {
    return;
  }


  element.textContent =
    message;


  element.className =
    `status ${type}`;
}