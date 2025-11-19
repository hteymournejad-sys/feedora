<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::all();
        return view('questions.index', compact('questions'));
    }

    public function store(Request $request)
    {
        return redirect()->back();
    }

    public function exit()
    {
        return redirect()->route('home')->with('message', 'ارزیابی با موفقیت پایان یافت.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        \Log::info('Excel Headers:', ['headers' => $rows[0]]);
        \Log::info('Excel Rows:', ['rows' => array_slice($rows, 1, 5)]);

        Question::query()->delete();

        $headers = array_shift($rows);

        $headers = array_map(function ($header) {
            $header = trim(str_replace("\u{FEFF}", '', (string)$header));
            $header = strtolower($header);
            $header = str_replace(' ', '', $header);
            if (in_array($header, ['subcategory', 'sub category', 'sub_category'])) {
                $header = 'subcategory';
            }
            return $header;
        }, $headers);

        \Log::info('Cleaned Headers:', ['headers' => $headers]);

        if (!in_array('id', $headers)) {
            \Log::error('Header "id" not found in headers', ['headers' => $headers]);
            return redirect()->back()->with('error', 'ستون "id" در فایل اکسل یافت نشد.');
        }

        foreach ($rows as $index => $row) {
            if (count($row) < count($headers)) {
                \Log::warning('Row has fewer columns than headers', [
                    'row_index' => $index + 1,
                    'row' => $row,
                ]);
                continue;
            }

            $rowData = array_combine($headers, $row);

            \Log::info('Raw Row Data:', ['row_index' => $index + 1, 'row' => $row]);
            \Log::info('Mapped Row Data:', ['row_index' => $index + 1, 'data' => $rowData]);

            if (!isset($rowData['id']) || empty($rowData['id'])) {
                \Log::error('ID is missing or empty in row', [
                    'row_index' => $index + 1,
                    'row' => $row,
                ]);
                return redirect()->back()->with('error', "ستون id در ردیف " . ($index + 1) . " خالی است.");
            }

            $rowId = (int)$rowData['id'];
            \Log::info('Assigned ID for Row:', ['row_index' => $index + 1, 'id' => $rowId]);

            Question::create([
                'id' => $rowId,
                'text' => $rowData['text'] ?? 'سوال نامشخص',
                'domain' => $rowData['domain'] ?? null,
                'subcategory' => $rowData['subcategory'] ?? null,
                'weight' => $rowData['weight'] ?? 0,
                'applicable_small' => filter_var($rowData['applicable_small'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'applicable_medium' => filter_var($rowData['applicable_medium'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'applicable_large' => filter_var($rowData['applicable_large'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'applicable_manufacturing' => filter_var($rowData['applicable_manufacturing'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'applicable_service' => filter_var($rowData['applicable_service'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'applicable_distribution' => filter_var($rowData['applicable_distribution'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'applicable_investment' => filter_var($rowData['applicable_investment'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'applicable_project' => filter_var($rowData['applicable_project'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'applicable_university' => filter_var($rowData['applicable_university'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'applicable_research' => filter_var($rowData['applicable_research'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'applicable_hospital' => filter_var($rowData['applicable_hospital'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'applicable_banking' => filter_var($rowData['applicable_banking'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'description' => $rowData['description'] ?? null,
                'guide' => $rowData['guide'] ?? null,
                'risks' => $rowData['risks'] ?? null,
                'strengths' => $rowData['strengths'] ?? null,
                'current_status' => $rowData['current_status'] ?? null,
                'improvement_opportunities' => $rowData['improvement_opportunities'] ?? null,
                'Maturity_level' => $rowData['maturity_level'] ?? $rowData['Maturity_level'] ?? null, // چک دو حالتی
            ]);
        }

        return redirect()->back()->with('success', 'سوالات با موفقیت وارد شدند!');
    }
}