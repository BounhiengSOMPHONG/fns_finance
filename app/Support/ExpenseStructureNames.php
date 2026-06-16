<?php

namespace App\Support;

class ExpenseStructureNames
{
    /**
     * Canonical section/subsection names from the expense workbook.
     */
    public static function names(): array
    {
        return [
            '2.1' => 'ແຜນງົບປະມານລາຍຈ່າຍບໍລິຫານປົກກະຕິຂອງ ຄວທ',
            '2.1.1' => 'ບໍລິຫານສັງລວມ',
            '2.1.2' => 'ບຳລຸງຮັກສາ, ສ້ອມແປງ ແລະ ຕິດຕັ້ງ',
            '2.1.3' => 'ສ້ອມແປງ ແລະ ປັບປຸງອາຄານຫ້ອງຮຽນ',
            '2.1.4' => 'ສ້ອມແປງພາຫານະ',
            '2.1.5' => 'ຊື້ເຄື່ອງຈັກ, ວັດຖຸອຸປະກອນ',
            '2.1.6' => 'ຄ່າປະກັນໄພພາຫະນະ',
            '2.1.7' => 'ລາຍຈ່າຍໄປວຽກທາງການ',
            '2.1.8' => 'ປົກປັກຮັກສາ ແລະ ອານາໄມອາຄານ, ສະຖານທີ່',
            '2.1.9' => 'ວຽກງານກິດຈະກຳນັກສຶກສາ',
            '2.1.10' => 'ລາຍຈ່າຍກອງປະຊຸມ, ສຳມະນາ ແລະ ຝຶກອົບຮົມ',
            '2.1.10.1' => 'ລາຍຈ່າຍກອງປະຊຸມ',
            '2.1.10.2' => 'ລາຍຈ່າຍສຳມະນາ',
            '2.1.10.3' => 'ລາຍຈ່າຍຝຶກອົບຮົມ',
            '2.1.11' => 'ລາຍຈ່າຍບໍລິຫານປົກກະຕິອື່ນໆ',
            '2.2' => 'ແຜນງົບປະມານລາຍຈ່າຍປັບປຸງ ແລະ ສົ່ງເສີມວິຊາການຂອງ ຄວທ',
            '2.2.1' => 'ຊື້ວັດຖຸ, ອຸປະກອນການຮຽນ ແລະ ການສິດສອນ',
            '2.2.2' => 'ປັບປຸງແລະພັດທະນາການສຶກສາ (ປັບປຸງຫຼັກສູດ, ພິມປື້ມ)',
            '2.2.2.1' => 'ງົບປະມານໃນການສ້າງຕຳລາຮຽນ (ພິມປື້ມ)',
            '2.2.2.2' => 'ງົບປະມານໃນການພັດທະນາ ແລະ ປັບປຸງຫຼັກສູດ',
            '2.2.3' => 'ບຳລຸງຫ້ອງທົດລອງ',
            '2.2.4' => 'ຊື້ອຸປະກອນທົດລອງ',
            '2.2.5' => 'ລາຍຈ່າຍກອງປະຊຸມວິຊາການ',
            '2.2.6' => 'ບຳລຸງຫ້ອງອ່ານ',
            '2.2.7' => 'ການຍົກລະດັບໄລຍະຍາວ',
            '2.2.8' => 'ລາຍຈ່າຍຕິດຕາມການປະຕິບັດຫຼັກສູດ',
            '2.3' => 'ແຜນງົບປະມານລາຍຈ່າຍດັດສົມ, ສົ່ງເສີມ ແລະ ບຳລຸງຮັກສາຂອງ ຄວທ',
            '2.3.1' => 'ບໍລິຫານວິຊາການ',
            '2.3.2' => 'ບູລະນະ ແລະ ປົກປັກຮັກສາວັດທະນະທຳ',
            '2.3.3' => 'ໄປທັດສະນະສຶກສາ',
            '2.4' => 'ແຜນງົບປະມານລາຍຈ່າຍບໍລິຫານອຸດໜູນກົງຈັກ',
            '2.4.1' => 'ອຸດໜູນຄ່າບັດໂທລະສັບປະຈຳຕຳແໜ່ງ',
            '2.4.2' => 'ອຸດໜູນຄ່ານ້ຳມັນປະຈຳຕຳແໜ່ງ',
            '2.4.3' => 'ເງິນເດືອນສັນຍາຈ້າງ ແລະ ຄ່າແຮງງານ',
            '2.4.4' => 'ອຸດໜູນການເຮັດວຽກເພີ່ມ',
            '2.4.5' => 'ອຸດໜູນຄ່າຄອງຊີບ',
            '2.5' => 'ແຜນງົບປະມານລາຍຈ່າຍຄ່າສິດສອນ ແລະ ການປະເມີນ',
            '2.5.1' => 'ຄ່າສອນລະບົບພິເສດ',
            '2.5.2' => 'ຄ່າບໍລິການສອບເສັງ',
            '2.5.3' => 'ບົດໂຄງການຈົບຊັ້ນ',
            '2.5.4' => 'ອຸດໜູນການລົງທະບຽນ',
            '2.6' => 'ແຜນລາຍຈ່າຍເຄື່ອນໄຫວນອກຫຼັກສູດ',
            '2.6.1' => 'ບໍລິຈາກເລືອດໃຫ້ອົງການກາແດງລາວ',
            '2.6.2' => 'ເຄື່ອນໄຫວກິລາ ແລະ ສິນລະປະ',
            '2.6.3' => 'ອອກແຮງງານລວມ',
            '2.6.4' => 'ຈັດການແຂ່ງຂັນຖາມ-ຕອບວິທະຍາສາດ',
        ];
    }

    public static function nameFor(string $code): ?string
    {
        return self::names()[$code] ?? null;
    }

    public static function fallbackSectionName(string $code): string
    {
        return self::nameFor($code) ?? 'ກຸ່ມລາຍຈ່າຍ '.$code;
    }

    public static function fallbackSubsectionName(string $code): string
    {
        return self::nameFor($code) ?? 'ລາຍການ '.$code;
    }

    public static function codeSortKey(string $code): string
    {
        return collect(explode('.', $code))
            ->map(fn (string $part) => str_pad((string) ((int) $part), 4, '0', STR_PAD_LEFT))
            ->implode('.');
    }

    public static function isPlaceholder(?string $value, ?string $code = null): bool
    {
        $value = trim((string) $value);

        if ($value === '') {
            return true;
        }

        $placeholders = [
            'Default row name',
            'Expense row',
            '-',
        ];

        if (in_array($value, $placeholders, true)) {
            return true;
        }

        return $code !== null && in_array($value, [
            'ກຸ່ມລາຍຈ່າຍ '.$code,
            'ລາຍການ '.$code,
        ], true);
    }

    public static function defaultRowCanonicalName(string $code, int $sortOrder): ?string
    {
        return [
            '2.1.1' => [
                1 => 'ເຄື່ອງໃຊ້ຫ້ອງການ',
            ],
            '2.2.2.1' => [
                1 => 'ພາກວິຊາຄະນິດສາດ',
            ],
            '2.2.4' => [
                1 => 'ອຸປະກອນທົດລອງຟິຊິກສາດ',
            ],
        ][$code][$sortOrder] ?? null;
    }

    public static function knownDefaultRowTypos(string $code, int $sortOrder): array
    {
        return [
            '2.1.1' => [
                1 => [
                    'ຊືເຄື່ອງໃຊ້ຫ້ອງການ',
                    'ຊື້ເຄື່ອງໃຊ້ຫ້ອງການ',
                ],
            ],
            '2.2.2.1' => [
                1 => [
                    'ພາວິຊາຄະນິດສາດ',
                ],
            ],
            '2.2.4' => [
                1 => [
                    'ອູປະກອນທົດລອງຟິຊິກສາດ',
                ],
            ],
        ][$code][$sortOrder] ?? [];
    }

    public static function defaultRowTargetName(string $code, int $sortOrder, ?string $currentName): ?string
    {
        $target = self::defaultRowCanonicalName($code, $sortOrder);

        if ($target === null) {
            return null;
        }

        $currentName = trim((string) $currentName);
        if (self::isPlaceholder($currentName, $code) || in_array($currentName, self::knownDefaultRowTypos($code, $sortOrder), true)) {
            return $target;
        }

        return null;
    }
}
