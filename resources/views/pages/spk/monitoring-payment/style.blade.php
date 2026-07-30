<style>
    #itemTableBody input,
#itemTableBody textarea{

    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    outline: none !important;

    padding:2px 4px;

    width:100%;

    /* text-align:center; */

}
.item-code {
    max-width: 180px;   /* sesuaikan */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.item-a {
    max-width: 100px;   /* sesuaikan */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.item-b {
    max-width: 90px;   /* sesuaikan */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.card-header{
    background: #fff;
    border-bottom: 1px solid #dee2e6;
}

#searchTable{
    padding-left: 15px;
}

#sortBy{
    min-width: 180px;
}
.po-divider td{
    border-bottom:4px solid #0d6efd !important;
}
.table-responsive{
    max-height: 75vh; /* sesuaikan tinggi */
    overflow: auto;
}

.table-responsive table{
    border-collapse: separate;
    border-spacing: 0;
}

.table-responsive thead th{
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f8f9fa; /* sama seperti table-light */
    box-shadow: inset 0 -1px 0 #dee2e6;
}
#itemTableBody textarea{

    resize:none;
    overflow:hidden;

}

#itemTableBody input:focus,
#itemTableBody textarea:focus{

    background:#fff8d6 !important;
    border-bottom:2px solid #0d6efd !important;

}.approved-by{
    display:inline-block;
    max-width:120px;   /* sesuaikan */
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    vertical-align:middle;
}
@keyframes blink {
    0%   { background:#ffe5e5; }
    50%  { background:#ff8080; }
    100% { background:#ffe5e5; }
}
/* ==========================================================================
   Commercial Invoice & Export Packing List Table Styling
   File: resources/views/pages/exports/partials/style.blade.php
   ========================================================================== */

/* 1. Card & Wrapper Shadow */
.card {
  border: 1px solid #e2e8f0 !important;
  border-radius: 0.75rem !important;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;
  transition: all 0.2s ease-in-out;
}

.card-header.bg-primary {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
  border-bottom: 1px solid #334155 !important;
  padding: 0.85rem 1.25rem !important;
}

.card-header h5 {
  font-weight: 700 !important;
  letter-spacing: -0.01em;
  font-size: 1.05rem !important;
}

/* 2. Responsive Table Wrapper */
.table-responsive {
  border-radius: 0 0 0.75rem 0.75rem;
  overflow-x: auto;
}

.table {
  margin-bottom: 0 !important;
  font-size: 0.825rem !important;
  color: #1e293b !important;
  border-color: #cbd5e1 !important;
}

/* 3. Table Header Styling */
.table thead.table-light {
  background-color: #f8fafc !important;
}

.table thead th {
  background-color: #f1f5f9 !important;
  color: #0f172a !important;
  font-weight: 700 !important;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  font-size: 0.725rem !important;
  padding: 0.65rem 0.5rem !important;
  vertical-align: middle !important;
  border-bottom: 2px solid #94a3b8 !important;
  border-right: 1px solid #cbd5e1 !important;
}

.table thead th.table-secondary {
  background-color: #e2e8f0 !important;
  color: #334155 !important;
  font-size: 0.75rem !important;
}

/* 4. Table Body & Row Hover Effects */
.table tbody tr {
  transition: background-color 0.15s ease-in-out;
}

.table tbody tr:nth-child(even) {
  background-color: #f8fafc;
}

.table-hover tbody tr:hover {
  background-color: #f1f5f9 !important;
}

.table tbody td {
  padding: 0.45rem 0.4rem !important;
  vertical-align: middle !important;
  border-color: #e2e8f0 !important;
}

/* 5. Input Fields inside Table Cells */
.table .form-control,
.table .form-control-sm {
  font-size: 0.8rem !important;
  border-color: #cbd5e1 !important;
  border-radius: 0.375rem !important;
  padding: 0.25rem 0.4rem !important;
  background-color: #ffffff !important;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.table .form-control:focus,
.table .form-control-sm:focus {
  border-color: #0284c7 !important;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15) !important;
  background-color: #ffffff !important;
}

.table .form-control[readonly] {
  background-color: #f1f5f9 !important;
  color: #475569 !important;
  font-weight: 600 !important;
}

.table textarea.form-control-sm {
  min-height: 44px;
  resize: vertical;
}

/* 6. Product Image Thumbnail Zoom Effect */
.table img.img-thumbnail {
  border-radius: 0.375rem !important;
  border: 1px solid #cbd5e1 !important;
  padding: 0.125rem !important;
  background-color: #ffffff !important;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  object-fit: cover;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.table img.img-thumbnail:hover {
  transform: scale(1.4);
  z-index: 20;
  position: relative;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

/* 7. Table Footer (Totals Row) */
.table tfoot {
  background-color: #f1f5f9 !important;
  border-top: 2px solid #0f172a !important;
  font-weight: 700 !important;
  color: #0f172a !important;
}

.table tfoot td {
  padding: 0.6rem 0.5rem !important;
  font-size: 0.825rem !important;
  border-top: 2px solid #334155 !important;
}

#grandTotalPrice {
  color: #0d9488 !important; /* Elegant teal total price */
  font-size: 0.9rem !important;
}

/* 8. Action Buttons */
.btn-outline-danger.remove-item,
.btn-danger.remove-item {
  padding: 0.2rem 0.45rem !important;
  font-size: 0.75rem !important;
  border-radius: 0.375rem !important;
  transition: all 0.15s ease-in-out;
}

.btn-danger.remove-item:hover {
  background-color: #dc2626 !important;
  border-color: #dc2626 !important;
  transform: translateY(-1px);
}

/* 9. Modal Search Table Enhancements */
#modalAddPo .modal-header {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
}

#combineItemTable thead th {
  background-color: #0f172a !important;
  color: #ffffff !important;
  font-size: 0.75rem !important;
  text-transform: uppercase;
}

#combinePoResult .list-group-item {
  border-left: 3px solid transparent;
  transition: all 0.15s ease;
}

#combinePoResult .list-group-item:hover {
  border-left-color: #0284c7;
  background-color: #f0f9ff;
}
#modalAddPo .modal-dialog {
    max-width: 95vw !important;
    width: 95vw;
}

#modalAddPo .modal-content {
    height: 90vh;
}

#modalAddPo .modal-body {
    overflow-y: auto;
}
</style>
