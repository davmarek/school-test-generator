@php
    use App\Enums\QuestionType;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $test->name }}</title>
    <style>
        @font-face {
            font-family: 'Geist';
            font-style: normal;
            font-weight: normal;
            src: url({{ storage_path('fonts/Geist-Regular.ttf') }}) format('truetype');
        }

        @font-face {
            font-family: 'Geist';
            font-style: normal;
            font-weight: bold;
            src: url({{ storage_path('fonts/Geist-Bold.ttf') }}) format('truetype');
        }

        body {
            font-family: 'Geist', sans-serif;
            line-height: 1.5;
            color: #1a202c;
        }

        .header {
            margin-bottom: 2rem;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
            padding: 0.5rem;
            line-height: 1;
            border: 1px solid #cbd5e0;
        }

        .field {
            border-bottom: 1px solid #cbd5e0;
            min-width: 150px;
            display: inline-block;
        }

        .test-title {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .question {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .question-header {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .points {
            float: right;
            font-size: 12px;
            color: #718096;
        }

        .true-false-table {
            width: 100%;
            border-collapse: collapse;

        }

        .true-false-table th {
            background-color: #f7fafc;
        }

        .true-false-table td,
        .true-false-table th {
            text-align: left;
            border: 1px solid #cbd5e0;
            padding: 0.4rem 0.5rem 0.7rem 0.5rem;
        }

        .true-false-table td:nth-child(1),
        .true-false-table th:nth-child(1) {
            width: 70%;
        }

        .true-false-table td:nth-child(2),
        .true-false-table th:nth-child(2) {
            width: 15%;
        }

        .true-false-table td:nth-child(3),
        .true-false-table th:nth-child(3) {
            width: 15%;
        }

        .checkbox,
        .radio {
            vertical-align: middle;
        }

        .checkbox {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 1px solid #000;
            margin-right: 8px;
        }

        .radio {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 1px solid #000;
            border-radius: 50%;
            margin-right: 8px;
        }


        .closed-options-table {
            width: 100%;
            border-collapse: collapse;
        }

        .closed-options-table tr {
            padding-bottom: 1rem;
        }

        .closed-options-table td:nth-child(1) {
            width: auto;
            padding-top: 0.7rem;
            padding-right: 0.5rem;
            vertical-align: top;
        }

        .closed-options-table td:nth-child(2) {
            width: 100%;
            padding-bottom: 0.8rem;
        }

        .line-answer {
            border-bottom: 1px solid #cbd5e0;
            height: 20px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    @foreach ($generatedTests as $testIndex => $questions)
        <div style="{{ !$loop->last ? 'page-break-after: always;' : '' }}">
            <div class="header">
                <div class="test-title">{{ $test->name }}</div>
                <table class="header-table">
                    <tr>
                        <td>Jméno:</td>
                        <td width="30%">Třída:</td>
                    </tr>
                    <tr>
                        <td>Datum:</td>
                        <td width="30%">Body:</td>
                    </tr>
                </table>
            </div>

            @foreach ($questions as $index => $question)
                <div class="question">
                    <div class="question-header">
                        <span class="points">({{ $question->calculated_points }} pts)</span>
                        {{ $index + 1 }}. {{ $question->text }}
                    </div>

                    @if ($question->type === QuestionType::CLOSED)
                        @php
                            $correctCount = $question->options->where('is_correct', true)->count();
                            $iconClass = $correctCount <= 1 ? 'radio' : 'checkbox';
                        @endphp
                        <table class="closed-options-table">
                            @foreach ($question->options as $option)
                                <tr>
                                    <td><span class="{{ $iconClass }}"></span></td>
                                    <td>{{ $option->text }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @elseif($question->type === QuestionType::TRUE_FALSE)
                        <table class="true-false-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Pravda</th>
                                    <th>Nepravda</th>
                                </tr>
                            </thead>
                            @foreach ($question->options as $option)
                                <tr>
                                    <td>{{ $option->text }}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endforeach
                        </table>
                    @elseif($question->type === QuestionType::OPEN)
                        <div class="line-answer"></div>
                        <div class="line-answer"></div>
                        <div class="line-answer"></div>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach
</body>

</html>
