<?php
use App\Enums\QuestionType;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $test->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
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

        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            margin-right: 8px;
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
                        <div class="options">
                            @foreach ($question->options as $option)
                                <div class="option">
                                    <span class="checkbox"></span> {{ $option->text }}
                                </div>
                            @endforeach
                        </div>
                    @elseif($question->type === QuestionType::TRUE_FALSE)
                        <table class="true-false-table">
                            <thead>
                                <tr>
                                    <th>Otázka</td>
                                    <th>Pravda</td>
                                    <th>Nepravda</td>
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
