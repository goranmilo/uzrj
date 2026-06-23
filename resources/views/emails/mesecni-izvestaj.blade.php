<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesečni izveštaj</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #10B981;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }
        .section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .section h3 {
            color: #10B981;
            margin-top: 0;
            border-bottom: 2px solid #10B981;
            padding-bottom: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .status-success { background-color: #d1fae5; color: #065f46; }
        .status-warning { background-color: #fef3c7; color: #92400e; }
        .status-danger { background-color: #fee2e2; color: #991b1b; }
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            background-color: #10B981;
            transition: width 0.3s;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #6b7280;
        }
        ul { list-style: none; padding: 0; }
        li { padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        li:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Mesečni izveštaj</h1>
        <p>Poštovani/a {{ $clan->ime }},</p>
    </div>

    <div class="content">
        {{-- Status članarine --}}
        <div class="section">
            <h3>💰 Status članarine</h3>
            @if($clanarina)
                <p><strong>Period:</strong> {{ $clanarina->period->naziv }}</p>
                <p><strong>Zaduženo:</strong> {{ number_format($clanarina->iznos_zaduzenja, 2) }} RSD</p>
                <p><strong>Plaćeno:</strong> {{ number_format($clanarina->iznos_placen, 2) }} RSD</p>
                <p><strong>Dug:</strong> 
                    <span class="status-badge status-danger">
                        {{ number_format($clanarina->iznos_zaduzenja - $clanarina->iznos_placen, 2) }} RSD
                    </span>
                </p>
            @else
                <p class="status-badge status-success">Nema dugovanja</p>
            @endif
        </div>

        {{-- Bodovi --}}
        <div class="section">
            <h3>⭐ Bodovi</h3>
            @if($statusBodova)
                <p><strong>Godišnji minimum:</strong> {{ $statusBodova['bodovi_godina'] }} / {{ $statusBodova['godisnji_minimum'] }} bodova</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $statusBodova['procenat_godina'] }}%"></div>
                </div>
                
                <p><strong>Ukupan prag:</strong> {{ $statusBodova['bodovi_period'] }} / {{ $statusBodova['ukupan_prag'] }} bodova</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $statusBodova['procenat_period'] }}%"></div>
                </div>

                @if(!$statusBodova['ispunjava_godisnji'])
                    <p class="status-badge status-danger">
                        ⚠️ Nedostaje {{ $statusBodova['preostalo_godina'] }} bodova za godišnji minimum
                    </p>
                @else
                    <p class="status-badge status-success">✓ Godišnji minimum ispunjen</p>
                @endif
            @endif
        </div>

        {{-- Predstojeće edukacije --}}
        <div class="section">
            <h3>📚 Predstojeće edukacije</h3>
            @if($edukacije->count() > 0)
                <ul>
                    @foreach($edukacije as $edukacija)
                        <li>
                            <strong>{{ $edukacija->naziv }}</strong><br>
                            📅 {{ $edukacija->datum_pocetka->format('d.m.Y H:i') }}<br>
                            📍 {{ $edukacija->lokacija }}<br>
                            ⭐ {{ $edukacija->bodovi }} bodova
                        </li>
                    @endforeach
                </ul>
            @else>
                <p>Nema zakazanih edukacija.</p>
            @endif
        </div>

        {{-- Poslednje edukacije --}}
        <div class="section">
            <h3>✅ Poslednje edukacije</h3>
            @if($bodovi->count() > 0)
                <ul>
                    @foreach($bodovi as $bod)
                        <li>
                            <strong>{{ $bod->edukacija->naziv ?? 'Ručni unos' }}</strong><br>
                            📅 {{ $bod->datum->format('d.m.Y') }}<br>
                            ⭐ +{{ $bod->bodovi }} bodova
                        </li>
                    @endforeach
                </ul>
            @else>
                <p>Nema evidentiranih edukacija.</p>
            @endif
        </div>

        {{-- Aktuelnosti --}}
        <div class="section">
            <h3>📢 Aktuelnosti</h3>
            @if($aktuelnosti->count() > 0)
                @foreach($aktuelnosti as $vest)
                    <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb;">
                        <h4>{{ $vest->naslov }}</h4>
                        <p style="font-size: 14px; color: #6b7280;">{{ $vest->datum_objave->format('d.m.Y') }}</p>
                        <p>{{ Str::limit($vest->sadrzaj, 200) }}</p>
                    </div>
                @endforeach
            @else>
                <p>Nema novih aktuelnosti.</p>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>Ovaj izveštaj je automatski generisan. Molimo vas da ne odgovarate na ovaj mejl.</p>
        <p>{{ config('app.name') }} | {{ now()->format('d.m.Y') }}</p>
    </div>
</body>
</html>
