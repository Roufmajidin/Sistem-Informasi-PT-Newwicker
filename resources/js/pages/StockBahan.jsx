import React, {
    useEffect,
    useState
} from 'react';

import axios from 'axios';

import {
    Workbook
} from '@fortune-sheet/react';

import '@fortune-sheet/react/dist/index.css';



export default function StockBahan() {

    const [sheetData, setSheetData] =
        useState([]);

    const [loading, setLoading] =
        useState(true);




    // =====================================
    // HELPER CELL
    // =====================================

    function setCell(
        celldata,
        r,
        c,
        value,
        bold = false,
        fontSize = 10,
        bg = '',
        color = '#000'
    ) {

        celldata.push({

            r,
            c,

            v: {

                v: value ?? '',

                m: String(value ?? ''),

                ct: {

                    fa: 'General',

                    t: 'g'

                },

                bl: bold ? 1 : 0,

                fs: fontSize,

                ff: 1,

                bg: bg,

                fc: color,

                ht: 0,

                vt: 0

            }

        });

    }




    // =====================================
    // LOAD
    // =====================================

    useEffect(() => {

        loadSheet();

    }, []);




    // =====================================
    // LOAD SHEET
    // =====================================

    async function loadSheet() {

        try {

            const response =
                await axios.get(
                    '/api/stocks'
                );

            const datas =
                response.data;


            const celldata = [];



            // =================================
            // TITLE
            // =================================

            setCell(

                celldata,

                0,

                5,

                'PT. Newwicker Indonesia',

                true,

                16

            );

            setCell(

                celldata,

                1,

                5,

                'Rekap Bahan Baku',

                true,

                14

            );




            // =================================
            // HEADER
            // =================================

            const headers = [

                'No',
                'Nama Barang',
                'Kode Barang',
                'Satuan',
                'Qty',
                'Harga / Qty',
                'Jumlah',
                'Gudang',
                'IN',
                'OUT',
                'SISA',
                'NO PO',
                'TANGGAL'

            ];


            headers.forEach(

                (header, col) => {

                    setCell(

                        celldata,

                        3,

                        col,

                        header,

                        true,

                        12,

                        '#dbe5f1',

                        '#000'

                    );

                }

            );




            // =================================
            // DATA
            // =================================

            datas.forEach(

                (item, index) => {

                    const row =
                        index + 4;


                    setCell(
                        celldata,
                        row,
                        0,
                        index + 1
                    );

                    setCell(
                        celldata,
                        row,
                        1,
                        item.nama_barang
                    );

                    setCell(
                        celldata,
                        row,
                        2,
                        item.kode_barang
                    );

                    setCell(
                        celldata,
                        row,
                        3,
                        item.satuan
                    );

                    setCell(
                        celldata,
                        row,
                        4,
                        parseInt(item.qty || 0)
                    );

                    setCell(
                        celldata,
                        row,
                        5,
                        rupiah(item.harga_qty)
                    );

                    setCell(
                        celldata,
                        row,
                        6,
                        item.jumlah
                    );

                    setCell(
                        celldata,
                        row,
                        7,
                        item.gudang
                    );

                    setCell(
                        celldata,
                        row,
                        8,
                        parseInt(item.in_qty || 0)
                    );

                    setCell(
                        celldata,
                        row,
                        9,
                        parseInt(item.out_qty || 0)
                    );

                    setCell(
                        celldata,
                        row,
                        10,
                        item.sisa
                    );

                    setCell(
                        celldata,
                        row,
                        11,
                        item.no_po
                    );

                    setCell(
                        celldata,
                        row,
                        12,
                        item.tanggal
                    );

                }

            );




            // =================================
            // SHEET
            // =================================

            setSheetData([

                {

                    name: 'Stock',

                    index: 0,

                    status: 1,

                    order: 0,

                    row: 300,

                    column: 20,

                    celldata: celldata,

                    config: {
                        merge: {

                            "0_5": {
                                r: 0,
                                c: 5,
                                rs: 1,
                                cs: 4
                            },

                            "1_5": {
                                r: 1,
                                c: 5,
                                rs: 1,
                                cs: 4
                            }

                        },

                        // =====================
                        // DEFAULT
                        // =====================

                        defaultRowHeight: 34,

                        defaultColWidth: 120,



                        // =====================
                        // COLUMN WIDTH
                        // =====================

                        columnlen: {

                            0: 60,
                            1: 320,
                            2: 180,
                            3: 100,
                            4: 90,
                            5: 150,
                            6: 150,
                            7: 160,
                            8: 90,
                            9: 90,
                            10: 100,
                            11: 110,
                            12: 150

                        },



                        // =====================
                        // ROW HEIGHT
                        // =====================

                        rowlen: {

                            0: 35,
                            1: 30,
                            2: 35,
                            3: 40

                        },



                        // =====================
                        // BORDER
                        // =====================

                        borderInfo: [

                            {

                                rangeType: 'range',

                                borderType:
                                    'border-all',

                                style: '1',

                                color: '#000',

                                range: [

                                    {

                                        row: [

                                            3,
                                            300

                                        ],

                                        column: [

                                            0,
                                            12

                                        ]

                                    }

                                ]

                            }

                        ]

                    }

                }

            ]);


            setLoading(false);

        }
        catch (err) {

            console.log(err);

        }

    }



    function rupiah(number) {

        return new Intl.NumberFormat(

            'id-ID'

        ).format(number || 0);

    }
    // =====================================
    // SAVE
    // =====================================

    async function saveSheet() {

        try {

            const sheet =
                sheetData?.[0];

            const matrix =
                sheet.data || [];


            const items = [];



            // =================================
            // LOOP ROW
            // =================================

            for (
                let r = 4;
                r < matrix.length;
                r++
            ) {

                const row =
                    matrix[r];

                if (!row) {
                    continue;
                }


                const nama =
                    row?.[1]?.v ?? '';

                const kode =
                    row?.[2]?.v ?? '';


                // =============================
                // SKIP EMPTY
                // =============================

                if (!kode && !nama) {
                    continue;
                }


                const qty =
                    Number(
                        row?.[4]?.v || 0
                    );

                const harga =
                    Number(
                        row?.[5]?.v || 0
                    );

                const inQty =
                    Number(
                        row?.[8]?.v || 0
                    );

                const outQty =
                    Number(
                        row?.[9]?.v || 0
                    );


                items.push({

                    nama_barang:
                        nama,

                    kode_barang:
                        kode,

                    satuan:
                        row?.[3]?.v ?? '',

                    qty:
                        qty,

                    harga_qty:
                        harga,

                    jumlah:
                        qty * harga,

                    gudang:
                        row?.[7]?.v ?? '',

                    in_qty:
                        inQty,

                    out_qty:
                        outQty,

                    sisa:
                        inQty - outQty,

                    no_po:
                        row?.[11]?.v ?? '',

                    tanggal:
                        row?.[12]?.v ?? ''

                });

            }



            const response =
                await axios.post(

                    '/api/stocks/save-sheet',

                    {

                        items: items

                    }

                );


            alert(
                response.data.message
            );

        }
        catch (err) {

            console.log(err);

            alert('Gagal save');

        }

    }




    // =====================================
    // LOADING
    // =====================================

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




    // =====================================
    // RENDER
    // =====================================

    return (

        <div>

            {/* ACTION BAR */}

            <div
                style={{

                    padding: '10px',

                    borderBottom:
                        '1px solid #ddd'

                }}
            >

                <button

                    onClick={saveSheet}

                    style={{

                        background: '#16a34a',

                        color: '#fff',

                        border: 'none',

                        padding: '10px 20px',

                        borderRadius: '5px',

                        cursor: 'pointer'

                    }}

                >

                    SAVE STOCK

                </button>

            </div>




            {/* SHEET */}

            <div
                style={{
                    height:
                        'calc(100vh - 60px)'
                }}
            >

                <Workbook

                    data={sheetData}

                    onChange={(data) => {

                        setSheetData(data);

                    }}

                />

            </div>

        </div>

    );

}
