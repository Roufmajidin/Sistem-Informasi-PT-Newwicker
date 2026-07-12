import React, {
    useEffect,
    useState
} from 'react';

import ReactDOM from 'react-dom/client';

import axios from 'axios';

import {
    Workbook
} from '@fortune-sheet/react';

import '@fortune-sheet/react/dist/index.css';



// =====================================================
// GLOBAL
// =====================================================

const spk =
    window.spkData || {};

const suppliers =
    window.suppliers || [];

const kategoriList =
    window.kategoriList || [];



// =====================================================
// APP
// =====================================================

function App() {


    // =================================================
    // STATE
    // =================================================

    const [sheetData, setSheetData] =
        useState([]);

    const [loading, setLoading] =
        useState(true);

    // const [sheetKey, setSheetKey] =
    //     useState(Date.now());



    // =================================================
    // DROPDOWN
    // =================================================

    const [selectedSupplier, setSelectedSupplier] =
        useState(

            spk.sup
            ||
            spk.nama
            ||
            suppliers?.[0]
            ||
            ''

        );


    const [selectedKategori, setSelectedKategori] =
        useState(

            spk.type
            ||
            kategoriList?.[0]
            ||
            ''

        );




    // =================================================
    // HELPER
    // =================================================

    function setCell(
        celldata,
        r,
        c,
        value
    ) {

        celldata.push({

            r,
            c,

            v: {

                v: value ?? '',

                m: String(
                    value ?? ''
                ),

                ct: {

                    fa: 'General',

                    t: 'g'

                }

            }

        });

    }




    // =================================================
    // LOAD
    // =================================================

    useEffect(() => {

        loadSheet();

    }, []);




    // =================================================
    // LOAD SHEET
    // =================================================

    function loadSheet() {

        try {

            const celldata = [];

            const startRow = 11;

            const paymentStartRow =

                startRow
                +
                (
                    spk.items?.length || 0
                )
                +
                3;


            const footerRow =

                paymentStartRow
                +
                (
                    spk.payments?.length || 0
                )
                +
                5;




            // =========================================
            // TITLE
            // =========================================

            setCell(
                celldata,
                0,
                0,
                'SURAT PERINTAH KERJA'
            );




            // =========================================
            // COMPANY
            // =========================================

            setCell(
                celldata,
                0,
                10,
                'PT. New Wicker Indonesia'
            );

            setCell(
                celldata,
                1,
                10,
                'Plumbon - Cirebon'
            );




            // =========================================
            // HEADER
            // =========================================

            setCell(
                celldata,
                5,
                9,
                'No. PO'
            );

            setCell(
                celldata,
                5,
                10,
                spk.no_po || ''
            );



            // =========================================
            // KATEGORI
            // =========================================

            setCell(
                celldata,
                6,
                9,
                'Kategori'
            );

            setCell(
                celldata,
                6,
                10,
                selectedKategori
            );



            // =========================================
            // NO SPK
            // =========================================

            setCell(
                celldata,
                5,
                0,
                'NO SPK'
            );

            setCell(
                celldata,
                5,
                1,
                spk.no_spk || ''
            );



            // =========================================
            // SUPPLIER
            // =========================================

            setCell(
                celldata,
                6,
                0,
                'SUPPLIER'
            );

            setCell(
                celldata,
                6,
                1,
                selectedSupplier
            );



            // =========================================
            // TGL TERIMA
            // =========================================

            setCell(
                celldata,
                7,
                0,
                'TGL TERIMA'
            );

            setCell(
                celldata,
                7,
                1,
                spk.tgl_terima || ''
            );



            // =========================================
            // TGL SELESAI
            // =========================================

            setCell(
                celldata,
                8,
                0,
                'TGL SELESAI'
            );

            setCell(
                celldata,
                8,
                1,
                spk.tgl_selesai || ''
            );




            // =========================================
            // TABLE HEADER
            // =========================================

            const headers = [

                'Kode Barang',
                'Gambar',
                'Nama Barang',
                'P',
                'L',
                'T',
                'Material',
                'QTY',
                '',
                'Harga',
                'Total',
                'Catatan'

            ];


            headers.forEach(

                (header, index) => {

                    setCell(

                        celldata,

                        10,

                        index,

                        header

                    );

                }

            );




            // =========================================
            // ITEMS
            // =========================================

            if (
                Array.isArray(
                    spk.items
                )
            ) {

                spk.items.forEach(

                    (item, index) => {

                        const row =
                            startRow + index;

                        let col = 0;



                        setCell(
                            celldata,
                            row,
                            col++,
                            item.kode || ''
                        );


                        setCell(
                            celldata,
                            row,
                            col++,
                            ''
                        );


                        setCell(
                            celldata,
                            row,
                            col++,
                            item.nama || ''
                        );


                        setCell(
                            celldata,
                            row,
                            col++,
                            item.p || ''
                        );


                        setCell(
                            celldata,
                            row,
                            col++,
                            item.l || ''
                        );


                        setCell(
                            celldata,
                            row,
                            col++,
                            item.t || ''
                        );


                        setCell(
                            celldata,
                            row,
                            col++,
                            item.material || ''
                        );


                        setCell(
                            celldata,
                            row,
                            col++,

                            item.pcs
                            ||
                            item.qty
                            ||
                            0

                        );


                        setCell(
                            celldata,
                            row,
                            col++,
                            item.set || 0
                        );


                        setCell(
                            celldata,
                            row,
                            col++,
                            item.harga || 0
                        );


                        setCell(
                            celldata,
                            row,
                            col++,
                            item.total || 0
                        );


                        setCell(
                            celldata,
                            row,
                            col++,

                            item.catatan?.remark
                            ||
                            ''

                        );

                    }

                );

            }




            // =========================================
            // PAYMENT HEADER
            // =========================================

            setCell(
                celldata,
                paymentStartRow,
                9,
                'Amount'
            );

            setCell(
                celldata,
                paymentStartRow,
                10,
                'Date'
            );

            setCell(
                celldata,
                paymentStartRow,
                11,
                'Note'
            );




            // =========================================
            // PAYMENTS
            // =========================================

            if (
                Array.isArray(
                    spk.payments
                )
            ) {

                spk.payments.forEach(

                    (payment, index) => {

                        const row =
                            paymentStartRow
                            +
                            1
                            +
                            index;


                        setCell(
                            celldata,
                            row,
                            9,
                            payment.amount || ''
                        );

                        setCell(
                            celldata,
                            row,
                            10,
                            payment.date || ''
                        );

                        setCell(
                            celldata,
                            row,
                            11,
                            payment.note || ''
                        );

                    }

                );

            }




            // =========================================
            // SHEET
            // =========================================

            setSheetData([

                {

                    name: 'SPK',

                    index: 0,

                    status: 1,

                    order: 0,

                    row:
                        footerRow + 10,

                    column: 20,

                    celldata: celldata,

                    config: {

                        merge: {

                            "0_0": {
                                r: 0,
                                c: 0,
                                rs: 1,
                                cs: 4
                            },

                            "5_1": {
                                r: 5,
                                c: 1,
                                rs: 1,
                                cs: 2
                            },

                            "6_1": {
                                r: 6,
                                c: 1,
                                rs: 1,
                                cs: 2
                            },

                            "7_1": {
                                r: 7,
                                c: 1,
                                rs: 1,
                                cs: 2
                            },

                            "8_1": {
                                r: 8,
                                c: 1,
                                rs: 1,
                                cs: 2
                            },

                            "5_10": {
                                r: 5,
                                c: 10,
                                rs: 1,
                                cs: 2
                            },

                            "6_10": {
                                r: 6,
                                c: 10,
                                rs: 1,
                                cs: 2
                            }

                        },



                        rowlen: {

                            0: 40,
                            10: 35,
                            11: 55,
                            12: 55,

                        },



                        columnlen: {

                            0: 140,
                            1: 120,
                            2: 260,
                            3: 60,
                            4: 60,
                            5: 60,
                            6: 260,
                            7: 60,
                            8: 60,
                            9: 100,
                            10: 120,
                            11: 180

                        }

                    }

                }

            ]);


            setLoading(false);

        }
        catch (err) {

            console.error(err);

            alert(
                'Gagal load spreadsheet'
            );

        }

    }




    // =================================================
    // UPDATE CELL
    // =================================================

    function updateCellValue(row, col, value) {

        setSheetData(prev => {

            const clone =
                JSON.parse(
                    JSON.stringify(prev)
                );

            const sheet =
                clone[0];


            if (!sheet.celldata) {
                sheet.celldata = [];
            }


            const existing =
                sheet.celldata.find(

                    item =>

                        item.r === row
                        &&
                        item.c === col

                );


            if (existing) {

                existing.v = {

                    v: value,

                    m: String(value),

                    ct: {

                        fa: 'General',

                        t: 'g'

                    }

                };

            }
            else {

                sheet.celldata.push({

                    r: row,

                    c: col,

                    v: {

                        v: value,

                        m: String(value),

                        ct: {

                            fa: 'General',

                            t: 'g'

                        }

                    }

                });

            }


            return clone;

        });


        // setSheetKey(Date.now());

    }




    // =================================================
    // CONVERT SHEET
    // =================================================
function convertSheetToSpk() {

    // =====================================
    // SHEET
    // =====================================

    const sheet =
        sheetData?.[0];

    if (!sheet) {

        return {

            items: [],

            payments: []

        };

    }


    // =====================================
    // DATA MATRIX
    // =====================================

    const matrix =
        sheet.data || [];


    // =====================================
    // GET CELL
    // =====================================

    function getCell(r, c) {

        return (
            matrix?.[r]?.[c]?.v
            ??
            ''
        );

    }



    // =====================================
    // ITEMS
    // =====================================

    const items = [];

    let row = 11;

    let index = 0;


    while (true) {

        const kode =
            getCell(row, 0);

        if (!kode) {
            break;
        }


        const originalItem =
            spk.items?.[index] || {};


        const detailId =

            originalItem.detail_po_id
            ||
            originalItem.detail_id
            ||
            null;


        const pcs =
            Number(
                getCell(row, 7) || 0
            );


        const set =
            Number(
                getCell(row, 8) || 0
            );


        const satuan =
            set > 0
                ? 'set'
                : 'pcs';


        items.push({

            detail_id:
                detailId,

            kode:
                getCell(row, 0),

            nama:
                getCell(row, 2),

            p:
                getCell(row, 3),

            l:
                getCell(row, 4),

            t:
                getCell(row, 5),

            material:
                getCell(row, 6),

            pcs:
                pcs,

            set:
                set,

            satuan:
                satuan,

            harga:
                Number(
                    getCell(row, 9) || 0
                ),

            total:
                Number(
                    getCell(row, 10) || 0
                ),

            images:
                originalItem.images || [],

            custom_columns:
                originalItem.custom_columns || [],

            catatan: {

                remark:
                    getCell(row, 11),

                images:
                    originalItem.catatan?.images || []

            }

        });


        row++;
        index++;

    }



    // =====================================
    // PAYMENTS
    // =====================================

    const payments = [];


    for (
        let r = row + 3;
        r <= row + 20;
        r++
    ) {

        const amount =
            getCell(r, 9);

        if (
            !amount
            ||
            amount === 'Amount'
        ) {
            continue;
        }


        const originalPayment =
            spk.payments?.[
                r - (row + 4)
            ] || {};


        payments.push({

            amount:
                String(amount),

            date:
                getCell(r, 10),

            note:
                getCell(r, 11),

            is_request:
                originalPayment.is_request || false,

            payment_id:
                originalPayment.payment_id || null,

            note_tambahan:
                originalPayment.note_tambahan || ''

        });

    }



    // =====================================
    // DEBUG
    // =====================================

    console.log(
        'FINAL ITEMS',
        items
    );


    // =====================================
    // RETURN
    // =====================================

    return {

        spk_type:
            selectedKategori,

        spk_id:

            spk.is_edit
                ? spk.id
                : null,

        no_po:
            spk.no_po || '',

        no_spk:
            getCell(5, 1),

        nama:
            selectedSupplier,

        tgl_terima:
            getCell(7, 1),

        tgl_selesai:
            getCell(8, 1),

        items:
            items,

        payments:
            payments,

        checked_types:
            spk.checked_types || [],

        custom_headers:
            spk.custom_headers || [],

        status:
            'draft'

    };

}




    // =================================================
    // SAVE
    // =================================================

    async function saveSpk() {

        try {

            const sheet =
                sheetData[0];


           const data =
    convertSheetToSpk();


            console.log(
                'DATA SAVE',
                data
            );


          const poId =

    spk.po_id
    ||
    spk.po?.id
    ||
    null;


if (!poId && !spk.is_edit) {

    alert('PO ID tidak ditemukan');

    return;
}


const url =

    spk.is_edit

        ? `/spk/update/${spk.id}`

        : `/spk/create/${poId}`;

            console.log(
                'SAVE URL',
                url
            );


            const response =
                await axios.post(
                    url,
                    data
                );


            alert(
                response.data.message
            );

        }
        catch (err) {

            console.error(err);

            console.log(
                err.response?.data
            );

            alert(

                err.response?.data?.message
                ||
                'Gagal save'

            );

        }

    }




    // =================================================
    // LOADING
    // =================================================

    if (loading) {

        return (
            <div
                style={{
                    padding: '20px'
                }}
            >
                Loading...
            </div>
        );

    }




    // =================================================
    // RENDER
    // =================================================

    return (

        <div>

            <div
                style={{

                    padding: '10px',

                    borderBottom:
                        '1px solid #ddd',

                    display: 'flex',

                    gap: '10px',

                    alignItems: 'center'

                }}
            >


                {/* SUPPLIER */}

                <select

                    value={selectedSupplier}

                    onChange={(e) => {

                        const value =
                            e.target.value;

                        setSelectedSupplier(value);

                        updateCellValue(
                            6,
                            1,
                            value
                        );

                    }}

                    style={{

                        padding: '8px',

                        minWidth: '200px'

                    }}

                >

                    {

                        suppliers.map((item, index) => (

                            <option
                                key={index}
                                value={item}
                            >

                                {item}

                            </option>

                        ))

                    }

                </select>




                {/* KATEGORI */}

                <select

                    value={selectedKategori}

                    onChange={(e) => {

                        const value =
                            e.target.value;

                        setSelectedKategori(value);

                        updateCellValue(
                            6,
                            10,
                            value
                        );

                    }}

                    style={{

                        padding: '8px',

                        minWidth: '200px'

                    }}

                >

                    {

                        kategoriList.map((item, index) => (

                            <option
                                key={index}
                                value={item}
                            >

                                {item}

                            </option>

                        ))

                    }

                </select>


  {

        spk.is_edit

            ? `EDIT SPK ID : ${spk.id}`

            : `CREATE SPK - PO ID : ${spk.po_id}`

    }

                {/* SAVE */}

                <button

                    onClick={saveSpk}

                    style={{

                        background: '#16a34a',

                        color: '#fff',

                        border: 'none',

                        padding: '10px 20px',

                        borderRadius: '6px',

                        cursor: 'pointer'

                    }}

                >
                    Save SPK
                </button>

            </div>




            {/* SHEET */}

            <div
                style={{
                    height: 'calc(100vh - 70px)'
                }}
            >

              <Workbook

    // key={sheetKey}

    data={sheetData}

    onChange={(data) => {

        console.log(
            'FORTUNE CHANGE',
            data
        );

        setSheetData(data);

    }}

/>

            </div>

        </div>

    );

}



// =====================================================
// RENDER
// =====================================================

const root =
    ReactDOM.createRoot(

        document.getElementById(
            'app'
        )

    );

root.render(

    <App />

);
