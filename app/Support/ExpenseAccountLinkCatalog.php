<?php

namespace App\Support;

use App\Models\ExpenseSubsectionDefaultRow;
use Illuminate\Support\Collection;

class ExpenseAccountLinkCatalog
{
    /**
     * Best-fit mappings for default rows that do not already have a trusted user link.
     * Keep suggestions conservative; rows marked review should stay visible in warning UI.
     */
    private const SUGGESTIONS = [
        '2.1.1' => [
            10 => ['code' => '61400600', 'review' => true],
        ],
        '2.1.8' => [
            '*' => ['code' => '62200201', 'review' => true],
        ],
        '2.1.9' => [
            '*' => ['code' => '63300100', 'review' => true],
        ],
        '2.2.2.1' => [
            '*' => ['code' => '63300100', 'review' => true],
        ],
        '2.2.2.2' => [
            '*' => ['code' => '63300100', 'review' => true],
        ],
        '2.2.5' => [
            1 => ['code' => '62400100', 'review' => true],
            2 => ['code' => '62400200', 'review' => true],
            3 => ['code' => '62400100', 'review' => true],
        ],
        '2.2.6' => [
            '*' => ['code' => '66300000', 'review' => true],
        ],
        '2.2.7' => [
            '*' => ['code' => '62400300', 'review' => true],
        ],
        '2.2.8' => [
            '*' => ['code' => '62300100', 'review' => true],
        ],
        '2.3.1' => [
            '*' => ['code' => '63300100', 'review' => true],
        ],
        '2.3.3' => [
            '*' => ['code' => '62300100', 'review' => true],
        ],
        '2.4.1' => [
            '*' => ['code' => '60200100', 'review' => true],
        ],
        '2.4.2' => [
            '*' => ['code' => '62100100', 'review' => true],
        ],
        '2.4.3' => [
            '*' => ['code' => '60100600', 'review' => true],
        ],
        '2.4.4' => [
            '*' => ['code' => '61400100', 'review' => true],
        ],
        '2.5.1' => [
            '*' => ['code' => '61400500', 'review' => false],
        ],
        '2.5.2' => [
            '*' => ['code' => '61400100', 'review' => true],
        ],
        '2.5.3' => [
            1 => ['code' => '61400300', 'review' => true],
            2 => ['code' => '61400300', 'review' => true],
            3 => ['code' => '61400400', 'review' => true],
        ],
        '2.6.1' => [
            '*' => ['code' => '61700600', 'review' => true],
        ],
        '2.6.2' => [
            '*' => ['code' => '63300100', 'review' => true],
        ],
        '2.6.3' => [
            '*' => ['code' => '62200201', 'review' => true],
        ],
        '2.6.4' => [
            '*' => ['code' => '62400100', 'review' => true],
        ],
    ];

    private const KNOWN_WRONG_LINKS = [
        // '2.x.y' => [sort_order => ['badcode']],
    ];

    public function suggestedAccountCode(ExpenseSubsectionDefaultRow $row): ?string
    {
        $suggestion = $this->suggestionFor($row);

        return $suggestion['code'] ?? null;
    }

    public function needsReview(ExpenseSubsectionDefaultRow $row): bool
    {
        $suggestion = $this->suggestionFor($row);

        if (($suggestion['review'] ?? false) === true) {
            return true;
        }

        return $row->chart_of_account_id === null && $suggestion !== null;
    }

    public function canAutoUpdate(ExpenseSubsectionDefaultRow $row): bool
    {
        if ($this->suggestedAccountCode($row) === null) {
            return false;
        }

        if ($row->chart_of_account_id === null) {
            return true;
        }

        $currentCode = $row->chartOfAccount?->account_code;
        if (! $currentCode) {
            return true;
        }

        return in_array($currentCode, $this->knownWrongCodes($row), true);
    }

    public function decorateRows(Collection $rows, Collection $accountsByCode): Collection
    {
        return $rows->map(function (ExpenseSubsectionDefaultRow $row) use ($accountsByCode): ExpenseSubsectionDefaultRow {
            $suggestedCode = $this->suggestedAccountCode($row);
            $row->setAttribute('suggested_account_code', $suggestedCode);
            $row->setAttribute('suggested_account', $suggestedCode ? $accountsByCode->get($suggestedCode) : null);
            $row->setAttribute('needs_review', $this->needsReview($row));
            $row->setAttribute('can_auto_update_account', $this->canAutoUpdate($row));

            return $row;
        });
    }

    private function suggestionFor(ExpenseSubsectionDefaultRow $row): ?array
    {
        $bySubsection = self::SUGGESTIONS[$row->subsection_code] ?? null;
        if ($bySubsection === null) {
            return null;
        }

        return $bySubsection[(int) $row->sort_order]
            ?? $bySubsection['*']
            ?? null;
    }

    private function knownWrongCodes(ExpenseSubsectionDefaultRow $row): array
    {
        return self::KNOWN_WRONG_LINKS[$row->subsection_code][(int) $row->sort_order] ?? [];
    }
}
