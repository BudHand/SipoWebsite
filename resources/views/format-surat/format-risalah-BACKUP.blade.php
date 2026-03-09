<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Risalah {{ $risalah->nomor_risalah }} </title>
    <style>
        @page {
            margin-top: 20px;
            margin-bottom: 0px;
            margin-left: 0;
            margin-right: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            padding-top: 120px;
            padding-bottom: 100px;
<<<<<<< HEAD
=======

>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
        }

        .first-page-adjust {
            height: 0px;
            margin-top: -120px;
        }

<<<<<<< HEAD
=======
        /* header/footer tetap */
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
        }

        main {
            margin-top: 5px;
            margin-bottom: 10px;
            text-align: center;
<<<<<<< HEAD
=======

>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
        }

        .content {
            width: 100%;
            margin: auto;
            text-align: center;
        }

<<<<<<< HEAD
=======
        /* blok info (Hari/Tempat/...) */
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
        .letter {
            margin-left: 2cm;
            margin-right: 2cm;
            background-color: #ffffff;
            line-height: 0.7cm;
            position: relative;
            z-index: 1;
        }

        .header1 tr td:first-child {
            width: 20%;
        }

        .header2 table {
            margin-top: 15px;
            border-collapse: collapse;
            width: 100%;
            table-layout: auto;
        }

        .header2 th {
            width: 50%;
            border-top: 3px solid black;
            border-bottom: 3px solid black;
            text-align: left;
            font-weight: normal;
            padding: 10px;
            word-wrap: break-word;
            overflow: hidden;
        }

<<<<<<< HEAD
=======
        /* netralisir .header2 th agar <colgroup> tabel utama berlaku */
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
        .header2 table.fill th {
            width: auto !important;
        }

        .header2 table.fill col {
            width: auto !important;
        }

        .header2 th+th {
            border-left: 3px solid black;
        }

        .header2 td {
            padding: 0;
            margin: 0;
            text-align: left;
            white-space: nowrap;
        }

        .header2 td:first-child {
            width: 1%;
            text-align: left;
            padding-right: 10px;
        }

<<<<<<< HEAD
        .pdf-mode .header2 {
            margin: 0 2cm;
=======
        /* >>> sejajarkan tabel dengan .letter (2 cm kiri–kanan) <<< */
        .pdf-mode .header2 {
            margin: 0 2cm;
            /* sama seperti .letter */
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
            padding: 0;
            width: auto;
        }

<<<<<<< HEAD
        /* Tabel utama */
=======
        /* tabel inti */
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
        .fill {
            border-collapse: collapse;
            font-size: 11px;
            table-layout: fixed;
<<<<<<< HEAD
=======
            /* kolom paten */
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
            width: 100%;
        }

        .fill th,
        .fill td {
            border: 1.5px solid black;
            padding: 6px;
            text-align: left;
            vertical-align: top;
            white-space: normal;
<<<<<<< HEAD
            word-wrap: break-word;
            word-break: break-word;
=======
            /* boleh turun baris */
            word-wrap: break-word;
            /* dompdf */
            word-break: break-word;
            /* potong kata panjang */
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
        }

        .fill thead th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .fill thead th:first-child {
            white-space: nowrap;
            text-align: center;
            font-size: 10px;
        }

        .fill td:first-child {
            white-space: nowrap;
            text-align: center;
        }

<<<<<<< HEAD
        /* ===== SUB RISALAH STYLING ===== */
        /* Identik dengan baris utama */
        .sub-row td {
            border: 1.5px solid black;
            padding: 6px;
            text-align: left;
            vertical-align: top;
            white-space: normal;
            word-wrap: break-word;
            word-break: break-word;
        }

        .sub-row td.sub-no {
            text-align: center;
            white-space: nowrap;
        }
=======
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4

        .contents {
            text-align: justify;
            line-height: 0.7cm;
        }

        .signature {
            margin-top: 5%;
            text-align: left !important;
            width: fit-content;
            margin-left: auto;
            margin-right: 3%;
        }

        .signature p {
            text-align: center;
            margin: 0;
        }

<<<<<<< HEAD
        /* view-mode */
=======
        /* view-mode (punyamu) */
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
        .view-mode header img,
        .view-mode footer img,
        .view-mode .content {
            width: 50%;
            margin: auto;
        }

        .view-mode header,
        .view-mode footer {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            position: fixed;
            left: 0;
            z-index: 100;
        }

        .view-mode {
            overflow: hidden;
        }

        .view-mode header img {
            display: block;
            margin: 0 auto;
            width: 50%;
        }

        .view-mode .header1 {
            position: fixed;
            top: 150px;
            left: 50%;
            transform: translateX(-50%);
            width: 40%;
            background-color: white;
            padding: 0;
            text-align: left;
            z-index: 1000;
        }

        .view-mode .header2 {
            position: relative;
            padding: 0;
            width: 39.5%;
            text-align: left;
        }

        .view-mode .fill {
            position: relative;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
            text-align: justify;
            padding: 0;
        }

        .view-mode .collab {
            position: relative;
            margin-top: 1cm;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
            text-align: justify;
            overflow-y: auto;
            max-height: calc(100vh - 9cm);
        }

<<<<<<< HEAD
        /* pdf-mode */
=======
        /* pdf-mode (punyamu) */
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
        .pdf-mode header img,
        .pdf-mode footer img,
        .pdf-mode .content {
            width: 100%;
        }

        .pdf-mode .date {
            text-align: center;
            width: 100%;
        }

        .pdf-mode .header2 h4,
        .pdf-mode .header2 p {
            text-align: left;
            margin-left: 0;
        }

        .pdf-mode .fill {
            position: relative;
            width: 100%;
            margin-left: auto;
            margin-right: auto;
            text-align: justify;
            padding: 0;
            margin-top: 0;
        }

        .pdf-mode .collab {
            position: relative;
            width: 100%;
            margin-left: 2.5px;
            margin-right: auto;
            text-align: justify;
            overflow-y: auto;
            max-height: calc(100vh - 12cm);
            padding: 0;
            margin-top: 0;
        }

        .date {
            margin-top: 10%;
            display: flex;
            justify-content: center;
            text-align: center;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .header1 tr td {
            line-height: 1.2;
        }

        .header2 h4,
        .header2 p,
        .header2 table td {
            line-height: 1.5;
        }
    </style>
</head>

<body class="{{ isset($isPdf) && $isPdf ? 'pdf-mode' : 'view-mode' }}">
    @php
        $status = strtolower((string) ($docStatus ?? ''));
        $needsWatermark = in_array($status, ['reject', 'correction', 'pending'], true);

        $file = match ($status) {
<<<<<<< HEAD
            'reject'     => public_path('assets/img/rejected-rotate-stamp.png'),
            'correction' => public_path('assets/img/oncorrection-rotate-stamp.png'),
            'pending'    => public_path('assets/img/onprogress-rotate-stamp.png'),
            default      => null,
=======
            'reject' => public_path('assets/img/rejected-rotate-stamp.png'),
            'correction' => public_path('assets/img/oncorrection-rotate-stamp.png'),
            'pending' => public_path('assets/img/onprogress-rotate-stamp.png'),
            default => null,
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
        };

        $wmBase64 =
            $needsWatermark && $file && file_exists($file)
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($file))
                : null;
    @endphp

    @if ($needsWatermark && $wmBase64)
        <style>
<<<<<<< HEAD
=======
            /* Overlay full-page di atas semua konten */
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
            ._wm_overlay {
                position: fixed;
                inset: 0;
                z-index: 9999;
<<<<<<< HEAD
                opacity: 0.4;
                pointer-events: none;
=======
                /* di atas semua elemen */
                opacity: 0.4;
                /* ubah transparansi sesuai selera */
                pointer-events: none;
                /* biar gak ganggu elemen lain */
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
                display: flex;
                align-items: center;
                justify-content: center;
            }
<<<<<<< HEAD
            ._wm_overlay img {
                width: 100%;
                height: 100%;
                object-fit: contain;
            }
        </style>
=======

            ._wm_overlay img {
                width: 100%;
                /* menutupi 1 halaman penuh */
                height: 100%;
                object-fit: contain;
                /* biar proporsional */
            }
        </style>

>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
        <div class="_wm_overlay">
            <img src="{{ $wmBase64 }}" alt="watermark">
        </div>
    @endif
<<<<<<< HEAD

=======
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
    <header>
        @if (isset($headerImage))
            <img src="{{ $headerImage }}" width="100%">
        @endif
    </header>

    <footer>
        @if (isset($footerImage))
            <img src="{{ $footerImage }}" width="100%">
        @endif
    </footer>

    <main>
        <div class="first-page-adjust"></div>
        <div class="content">
            <div class="date">
                <div class="title">
                    <h5>Risalah Rapat<br>
                        {{ $risalah->agenda }}
                        <br>Nomor: {{ $risalah->nomor_risalah }}
                    </h5>
                </div>
            </div>

            <div class="letter">
                <table style="font-size: 12px; margin-bottom: 20px;">
                    <tr>
                        <td style="width: 100px;">Hari, tanggal</td>
                        <td style="width: 10px;">:</td>
                        <td>{{ $risalah->tgl_dibuat->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td>Waktu</td>
                        <td>:</td>
                        <td>
                            {{ $risalah->waktu_mulai }}
                            @if (preg_match('/^\d{1,2}(\.\d{1,2})?$/', $risalah->waktu_mulai))
                                WIB
                            @endif
                            s.d
                            {{ $risalah->waktu_selesai ?? 'selesai' }}
                            @if ($risalah->waktu_selesai && preg_match('/^\d{1,2}(\.\d{1,2})?$/', $risalah->waktu_selesai))
                                WIB
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Tempat</td>
                        <td>:</td>
                        <td>{{ $risalah->tempat }}</td>
                    </tr>
<<<<<<< HEAD
                    <tr>
                        <td>Agenda</td>
                        <td>:</td>
                        <td>{!! nl2br(e($risalah->agenda)) !!}</td>
=======

                    <tr>
                        <td>Agenda</td>
                        <td>:</td>
                        <td> {!! nl2br(e($risalah->agenda)) !!}
                        </td>

>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
                    </tr>
                    <tr>
                        <td>Daftar Hadir</td>
                        <td>:</td>
                        <td>Daftar Hadir Terlampir</td>
                    </tr>
                    @if ($risalah->with_undangan)
                        <tr>
                            <td>Nomor Undangan</td>
                            <td>:</td>
                            <td>{{ $undangan->nomor_undangan }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            <div class="collab">
                <div class="header2">
                    <table class="fill" style="page-break-inside: always;">
<<<<<<< HEAD
                        <colgroup>
                            <col style="width:5%">
                            <col style="width:12%">
                            <col style="width:30%">
                            <col style="width:30%">
=======
                        <!-- total 100% -->
                        <colgroup>
                            <col style="width:6%">
                            <col style="width:16%">
                            <col style="width:26%">
                            <col style="width:29%"> <!-- Tindak Lanjut paling lebar -->
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
                            <col style="width:13%">
                            <col style="width:10%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Topik</th>
                                <th>Pembahasan</th>
                                <th>Tindak Lanjut</th>
                                <th>Target</th>
                                <th>PIC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($risalah->risalahDetails as $index => $detail)
<<<<<<< HEAD
                                {{-- ===== BARIS UTAMA ===== --}}
=======
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
                                <tr>
                                    <td style="text-align: center;">{{ $index + 1 }}</td>
                                    <td>{{ $detail->topik }}</td>
                                    <td>
                                        @foreach (explode(';', $detail->pembahasan) as $poin)
                                            {!! nl2br(e(trim($poin))) !!}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach (explode(';', $detail->tindak_lanjut) as $poin)
                                            {!! nl2br(e(trim($poin))) !!}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach (explode(';', $detail->target) as $poin)
                                            {!! nl2br(e(trim($poin))) !!}<br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach (explode(';', $detail->pic) as $poin)
                                            {!! nl2br(e(trim($poin))) !!}<br>
                                        @endforeach
                                    </td>
                                </tr>
<<<<<<< HEAD

                                {{-- ===== BARIS SUB RISALAH ===== --}}
                                @if ($detail->subDetails && $detail->subDetails->count() > 0)
                                    @foreach ($detail->subDetails as $subIndex => $sub)
                                            <tr class="sub-row">
                                            {{-- Kolom No: tampilkan label sub --}}
                                            <td class="sub-no"></td>
                                            <td>
                                                @if (!empty($sub->topik))
                                                    {{ $sub->topik }}
                                                @else

                                                @endif
                                            </td>
                                            <td>
                                                @if (!empty($sub->pembahasan))
                                                    @foreach (explode(';', $sub->pembahasan) as $poin)
                                                        {!! nl2br(e(trim($poin))) !!}<br>
                                                    @endforeach
                                                @else

                                                @endif
                                            </td>
                                            <td>
                                                @if (!empty($sub->tindak_lanjut))
                                                    @foreach (explode(';', $sub->tindak_lanjut) as $poin)
                                                        {!! nl2br(e(trim($poin))) !!}<br>
                                                    @endforeach
                                                @else

                                                @endif
                                            </td>
                                            <td>
                                                @if (!empty($sub->target))
                                                    @foreach (explode(';', $sub->target) as $poin)
                                                        {!! nl2br(e(trim($poin))) !!}<br>
                                                    @endforeach
                                                @else

                                                @endif
                                            </td>
                                            <td>
                                                @if (!empty($sub->pic))
                                                    @foreach (explode(';', $sub->pic) as $poin)
                                                        {!! nl2br(e(trim($poin))) !!}<br>
                                                    @endforeach
                                                @else

                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>

                    {{-- ===== TANDA TANGAN ===== --}}
                    <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                        <tr>
                            <!-- Kolom Kiri: Notulis Acara -->
                            <td style="width: 50%; text-align: center; vertical-align: top; padding: 10px; border: none;">
                                @php
                                    $jabatanNotulis   = $notulis?->position?->nm_position;
                                    $departemenNotulis = $notulis?->department?->name_department ?? '-';
                                @endphp
                                <p style="margin: 0; text-align:center">Notulis</p>
                                <p style="margin: 4px 0 0; text-align:center; white-space: normal; overflow-wrap: break-word; word-break: break-word;">
                                    {{ $jabatanNotulis }}
                                </p>
                                <p style="margin: 0; text-align:center; white-space: normal; overflow-wrap: break-word; word-break: break-word;">
                                    {{ $departemenNotulis }}
                                </p>
                                @if (!empty($risalah->qr_notulis_acara))
                                    <img src="data:image/png;base64,{{ $risalah->qr_notulis_acara }}" width="150" height="150" style="margin: 8px 0;">
                                @endif
                                <p style="margin: 4px 0; text-align:center">{{ $risalah->nama_notulis_acara }}</p>
                            </td>

                            <!-- Kolom Kanan: Pemimpin Acara -->
                            <td style="width: 50%; text-align: center; vertical-align: top; padding: 10px; border: none;">
                                @php
                                    $jabatanPemimpin   = $pemimpin?->position?->nm_position;
                                    $departemenPemimpin = $pemimpin?->department?->name_department ?? ($userBertandatangan?->divisi?->nm_divisi ?? '-');
                                @endphp
                                <p style="margin: 0; text-align:center">Pemimpin Acara</p>
                                <p style="margin: 4px 0 0; text-align:center; white-space: normal; overflow-wrap: break-word; word-break: break-word;">
                                    {{ $jabatanPemimpin }}
                                </p>
                                <p style="margin: 0; text-align:center; white-space: normal; overflow-wrap: break-word; word-break: break-word;">
                                    {{ $departemenPemimpin }}
                                </p>
                                @if (!empty($risalah->qr_pemimpin_acara))
                                    <img src="data:image/png;base64,{{ $risalah->qr_pemimpin_acara }}" width="150" height="150" style="margin: 8px 0;">
                                @endif
                                <p style="margin: 4px 0; text-align:center">{{ $risalah->nama_pemimpin_acara }}</p>
                            </td>
=======
                            @endforeach
                        </tbody>
                    </table>
                    {{-- <table style="width:100%; text-align:center; align:center;">
                        <tr>
                            <td width="50%" style="text-align: center">
                                @php
                                    $namaJabatan = $pemimpin?->position?->nm_position;
                                @endphp
                                <p style="text-align:center">Pemimpin Acara</p>
                                <p style="margin:0; text-align:center;">
                                    {{ $namaJabatan }}
                                    {{ $pemimpin?->department?->name_department ?? ($userBertandatangan?->divisi?->nm_divisi ?? '-') }}
                                </p>
                                @if (!empty($risalah->qr_pemimpin_acara))
                                    <img src="data:image/png;base64,{{ $risalah->qr_pemimpin_acara }}" width="150" height="150">
                                @endif
                                <p style="margin:0; text-align:center;">{{ $risalah->nama_pemimpin_acara }}</p>
                            </td>

                            <td width="50%" style="text-align: center">
                                @php
                                    $namaJabatan = $notulis?->position?->nm_position;
                                @endphp
                                <p style="text-align: center">Notulis</p>
                                <p style="margin:0; text-align: center; ">
                                    {{ $namaJabatan }}
                                    {{ $notulis?->department?->name_department ?? '-' }}
                                </p>
                                @if (!empty($risalah->qr_notulis_acara))
                                    <img src="data:image/png;base64,{{ $risalah->qr_notulis_acara }}" width="150" height="150">
                                @endif
                                <p style="margin:0; text-align: center;">{{ $risalah->nama_notulis_acara }}</p>
                            </td>
                        </tr>
                    </table> --}}
                    <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                        <tr>
                            <!-- Kolom Kiri: Notulis Acara -->
                            <td
                                style="width: 50%; text-align: center; vertical-align: top; padding: 10px; border: none;">
                                @php
                                    $jabatanNotulis = $notulis?->position?->nm_position;
                                    $departemenNotulis = $notulis?->department?->name_department ?? '-';
                                @endphp

                                <p style="margin: 0; text-align:center">Notulis</p>
                                <p
                                    style="margin: 4px 0 0; text-align:center; white-space: normal; overflow-wrap: break-word; word-break: break-word;">
                                    {{ $jabatanNotulis }}
                                </p>
                                <p
                                    style="margin: 0; text-align:center; white-space: normal; overflow-wrap: break-word; word-break: break-word;">
                                    {{ $departemenNotulis }}
                                </p>

                                @if (!empty($risalah->qr_notulis_acara))
                                    <img src="data:image/png;base64,{{ $risalah->qr_notulis_acara }}" width="150"
                                        height="150" style="margin: 8px 0;">
                                @endif

                                <p style="margin: 4px 0; text-align:center">{{ $risalah->nama_notulis_acara }}</p>
                            </td>
                            <!-- Kolom Kanan: Pemimpin -->
                            <td
                                style="width: 50%; text-align: center; vertical-align: top; padding: 10px; border: none;">
                                @php
                                    $jabatanPemimpin = $pemimpin?->position?->nm_position;
                                    $departemenPemimpin =
                                        $pemimpin?->department?->name_department ??
                                        ($userBertandatangan?->divisi?->nm_divisi ?? '-');
                                @endphp

                                <p style="margin: 0; text-align:center">Pemimpin Acara</p>
                                <p
                                    style="margin: 4px 0 0; text-align:center; white-space: normal; overflow-wrap: break-word; word-break: break-word;">
                                    {{ $jabatanPemimpin }}
                                </p>
                                <p
                                    style="margin: 0; text-align:center; white-space: normal; overflow-wrap: break-word; word-break: break-word;">
                                    {{ $departemenPemimpin }}
                                </p>

                                @if (!empty($risalah->qr_pemimpin_acara))
                                    <img src="data:image/png;base64,{{ $risalah->qr_pemimpin_acara }}" width="150"
                                        height="150" style="margin: 8px 0;">
                                @endif

                                <p style="margin: 4px 0; text-align:center">{{ $risalah->nama_pemimpin_acara }}</p>
                            </td>



>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
                        </tr>
                    </table>
                </div>
                <div style="clear: both;"></div>
<<<<<<< HEAD
            </div>
=======
            </div> <!-- /.header2 -->
        </div> <!-- /.collab -->
>>>>>>> f391d3bd7a36e2e77c053528b915428724fd6be4
        </div>
    </main>
</body>

</html>
