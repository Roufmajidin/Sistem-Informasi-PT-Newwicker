
<style>
    .spk-table td,
    .spk-table th {
        vertical-align: middle;
        padding: 6px;
    }

    .editable {
        background: #fff8dc;
        cursor: text;
    }

    .spk-wrapper {
        overflow-x: auto;
    }

    .image-box {
        min-height: 90px;
        border: 1px dashed #ccc;
        padding: 4px;
        display: flex;
        flex-wrap: wrap;
    }

    .preview-img {
        height: 70px;
        margin: 4px;
        border: 1px solid #3c3c3cff;
        border-radius: 4px;
    }

    .editable {
        min-height: 28px;
        border-bottom: 1px solid #999;
        padding: 4px;
        outline: none;
    }

    .editable:empty:before {
        content: attr(data-placeholder);
        color: #aaa;
    }

    .suggest-box {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #282828ff;
        z-index: 999;
        max-height: 160px;
        overflow-y: auto;
        display: none;
    }

    .suggest-item {
        padding: 6px 8px;
        cursor: pointer;
    }

    .suggest-item:hover {
        background: #f6e9c6;
    }

    .note-box img {
        height: 60px;
        margin: 3px;
    }

    .spk-textarea {
        width: 100%;
        border: none;
        resize: none;
        font-size: 11px;
        background: #fff8dc;
        line-height: 1.5;
    }

    .spk-textarea:focus {
        outline: none;
        background: #eef6ff;
    }

    @media print {
        .editable {
            background: none;
        }

        input[type=file] {
            display: none;
        }
    }

    .material {
        max-width: 200px;
        width: 200px;

        white-space: normal;
        /* AUTO WRAP */
        word-wrap: break-word;
        /* word lama */
        word-break: break-word;
        /* kata panjang */

        line-height: 1.4;
        vertical-align: top;
    }
    #supplierSugges {
         position:absolute;
            top:100%;
            left:0;
            right:0;
            background:#fff;
            border:1px solid #ccc;
            z-index:999;
            display:none;
            max-height:150px;
            overflow:auto;
    }
</style>
