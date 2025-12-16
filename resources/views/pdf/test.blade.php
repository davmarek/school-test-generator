<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $test->name }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.5; color: #1a202c; }
        .header { border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 30px; }
        .header-table { width: 100%; margin-bottom: 10px; }
        .header-table td { vertical-align: top; }
        .field { border-bottom: 1px solid #cbd5e0; min-width: 150px; display: inline-block; }
        .test-title { font-size: 24px; font-weight: bold; text-align: center; margin-bottom: 20px; }
        .question { margin-bottom: 20px; page-break-inside: avoid; }
        .question-header { font-weight: bold; margin-bottom: 5px; }
        .points { float: right; font-size: 12px; color: #718096; }
        .options { margin-left: 20px; margin-top: 10px; }
        .option { margin-bottom: 5px; }
        .checkbox { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; margin-right: 8px; }
        .line-answer { border-bottom: 1px solid #cbd5e0; height: 20px; margin-top: 20px; }
    </style>
</head>
<body>
    @foreach($generatedTests as $testIndex => $questions)
        <div style="{{ !$loop->last ? 'page-break-after: always;' : '' }}">
            <div class="header">
                <div class="test-title">{{ $test->name }}</div>
                <table class="header-table">
                    <tr>
                        <td style="width: 33%;">Name: ______________________</td>
                        <td style="width: 33%;">Class: ______________________</td>
                        <td style="width: 33%;">Date: ______________________</td>
                    </tr>
                </table>
            </div>

            @foreach($questions as $index => $question)
                <div class="question">
                    <div class="question-header">
                        <span class="points">({{ $question->calculated_points }} pts)</span>
                        {{ $index + 1 }}. {{ $question->text }}
                    </div>

                    @if($question->type === \App\Enums\QuestionType::CLOSED)
                        <div class="options">
                            @foreach($question->options as $option)
                                <div class="option">
                                    <span class="checkbox"></span> {{ $option->text }}
                                </div>
                            @endforeach
                        </div>
                    @elseif($question->type === \App\Enums\QuestionType::TRUE_FALSE)
                        <div class="options">
                            @foreach($question->options as $option)
                            <div class="option">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="width: 80%;">{{ $option->text }}</td>
                                            <td style="width: 10%;">[ T ]</td>
                                            <td style="width: 10%;">[ F ]</td>
                                        </tr>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    @elseif($question->type === \App\Enums\QuestionType::OPEN)
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
