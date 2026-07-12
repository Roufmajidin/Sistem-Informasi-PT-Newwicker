  <style>
        .freeze-wrapper {
            max-height: 600px;
            overflow: auto;
            position: relative;
            border: 1px solid #ddd;
        }
        /* ================= HEADER FREEZE ================= */
        #detail-table thead th {
            position: sticky;
            top: 0;
            background: #2b3c70ff;
            color: white;
            z-index: 20;
            border-bottom: 2px solid #ccc;
        }
        /* bayangan header */
        #detail-table thead {
            box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
        }
        /* ================= COLUMN WIDTH ================= */
        #detail-table th:nth-child(1),
        #detail-table td:nth-child(1) {
            min-width: 60px;
        }
        #detail-table th:nth-child(2),
        #detail-table td:nth-child(2) {
            min-width: 90px;
        }
        #detail-table th:nth-child(3),
        #detail-table td:nth-child(3) {
            min-width: 280px;
        }
        /* ================= FREEZE COL 1 ================= */
        #detail-table th:nth-child(1),
        #detail-table td:nth-child(1) {
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 10;
        }
        /* ================= FREEZE COL 2 ================= */
        #detail-table th:nth-child(2),
        #detail-table td:nth-child(2) {
            position: sticky;
            left: 60px;
            background: #fff;
            z-index: 10;
        }
        /* ================= FREEZE COL 3 ================= */
        #detail-table th:nth-child(3),
        #detail-table td:nth-child(3) {
            position: sticky;
            left: 150px;
            background: #fff;
            z-index: 10;
            box-shadow: 2px 0 6px rgba(0, 0, 0, .1);
        }
        /* ================= HEADER PRIORITY ================= */
        #detail-table thead th:nth-child(1),
        #detail-table thead th:nth-child(2),
        #detail-table thead th:nth-child(3) {
            z-index: 25;
            background: #2b3c70ff;
            /* biar tidak ketimpa putih */
        }
        /* ================= HOVER ================= */
        #detail-table tbody tr:hover td {
            background: #f9f9f9;
        }
        /* ================= OPTIONAL ================= */
        #detail-table th,
        #detail-table td {
            white-space: nowrap;
        }
        #detail-table td[data-key="remark"] {
            white-space: normal !important;
        }
        /* chat room */
        #chat-box {
            background: #efeae2;
            padding: 15px;
            height: 400px;
            overflow-y: auto;
            font-family: "Segoe UI", sans-serif;
        }
        /* container */
        .msg {
            display: flex;
            margin-bottom: 8px;
        }
        /* kiri */
        .msg.left {
            justify-content: flex-start;
        }
        /* kanan */
        .msg.right {
            justify-content: flex-end;
        }
        /* bubble */
        .bubble {
            max-width: 65%;
            padding: 6px 10px;
            border-radius: 8px;
            position: relative;
            font-size: 13px;
            line-height: 1.4;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);
        }
        /* kiri (orang lain) */
        .msg.left .bubble {
            background: #ffffff;
        }
        /* kanan (kita) */
        .msg.right .bubble {
            background: #d9fdd3;
        }
        /* nama (optional) */
        .msg .name {
            font-size: 11px;
            font-weight: 600;
            color: #667781;
            margin-bottom: 2px;
        }
        /* isi pesan */
        .msg .text {
            display: inline-block;
            word-break: break-word;
        }
        /* time kecil kanan bawah */
        .msg .time {
            font-size: 10px;
            color: #667781;
            float: right;
            margin-left: 8px;
        }
        /* tail kanan */
        .msg.right .bubble::after {
            content: "";
            position: absolute;
            right: -6px;
            top: 6px;
            border-left: 6px solid #d9fdd3;
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
        }
        /* tail kiri */
        .msg.left .bubble::after {
            content: "";
            position: absolute;
            left: -6px;
            top: 6px;
            border-right: 6px solid #fff;
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
        }
        /* tanggal separator */
        .date-separator {
            text-align: center;
            margin: 10px 0;
        }
        .date-separator span {
            background: rgba(255, 255, 255, 0.8);
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 11px;
            color: #54656f;
        }
        .chat-input-wrapper {
            display: flex;
            gap: 5px;
        }
        #chat-input {
            border-radius: 20px;
            padding: 8px 12px;
        }
        #btn-send-chat {
            border-radius: 10%;
            width: 60px;
            height: 40px;
        }
    </style>
