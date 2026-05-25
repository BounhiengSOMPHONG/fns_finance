<?php

namespace Database\Seeders;

use App\Models\SalaryBudgetCode;
use Illuminate\Database\Seeder;

class SalaryBudgetCodeSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
        SalaryBudgetCode::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Structure mirrors ຕາຕະລາງສັງລວມລາຍຈ່າຍເງິນເດືອນ from the PDF
        // annual_mode: 'x12' (monthly × 12), 'x1' (monthly × 1), 'direct' (annual entered directly)

        // ─── 60: ເງິນເດືອນ ແລະ ເງິນອຸດໜູນຂອງ ພ/ງ ───
        $cat60 = $this->node(null, '60', 'ເງິນເດືອນ ແລະ ເງິນອຸດໜູນຂອງ ພ/ງ', 1);

            // 60.10: ເງິນເດືອນພື້ນຖານພະນັກງານ
            $cat60_10 = $this->node($cat60->id, '10', 'ເງິນເດືອນພື້ນຖານພະນັກງານ', 1);
                $this->leaf($cat60_10->id, '01', 'ເງິນເດືອນ ພ/ງ ພວມປະຕິບັດງານ 100%', 1);
                $this->leaf($cat60_10->id, '02', 'ເງິນເດືອນເພື່ອເລື່ອນຊັ້ນ', 2);
                $this->leaf($cat60_10->id, '03', 'ເງິນເດືອນ ພ/ງ ເຂົ້າໃໝ່ 95%', 3);
                $this->leaf($cat60_10->id, '05', 'ເງິນເດືອນ ພ/ງ ຮຽນຕໍ່ຕ່າງປະເທດ', 4);
                $this->leaf($cat60_10->id, '06', 'ເງິນເດືອນ ພ/ງ ຕາມສັນຍາ', 5);

            // 60.20: ເງິນອຸດໜູນປົກກະຕິ
            $cat60_20 = $this->node($cat60->id, '20', 'ເງິນອຸດໜູນປົກກະຕິ', 2);
                $this->leaf($cat60_20->id, '01', 'ອຸດໜູນຕຳແໜ່ງ', 1);
                $this->leaf($cat60_20->id, '03', 'ອຸດໜູນອາຊີບ', 2);
                $this->leaf($cat60_20->id, '04', 'ອຸດໜູນອາຍຸການ', 3);
                $this->leaf($cat60_20->id, '05', 'ອຸດໜູນວຽກໜັກ-ທາງເບື່ອ', 4);
                $this->leaf($cat60_20->id, '07', 'ອຸດໜູນສອນຫ້ອງຄວບ', 5);
                $this->leaf($cat60_20->id, '08', 'ອຸດໜູນຄ່າຄອງຊີບ', 6);

        // ─── 61: ເງິນນະໂຍບາຍ ແລະ ຊ່ວຍໜູນຕ່າງໆ ───
        $cat61 = $this->node(null, '61', 'ເງິນນະໂຍບາຍ ແລະ ຊ່ວຍໜູນຕ່າງໆ', 2);

            // 61.20: ເງິນອຸດໜູນຄອບຄົວ
            $cat61_20 = $this->node($cat61->id, '20', 'ເງິນອຸດໜູນຄອບຄົວ', 1);
                $this->leaf($cat61_20->id, '1', 'ເງິນອຸດໜູນລູກພະນັກງານ', 1);
                $this->leaf($cat61_20->id, '2', 'ເງິນອຸດໜູນເມຍພະນັກງານ', 2);

            // 61.30: ເງິນນະໂຍບາຍກ່ອນຮັບບຳນານ
            $cat61_30 = $this->node($cat61->id, '30', 'ເງິນນະໂຍບາຍກ່ອນຮັບບຳນານ', 2);
                $this->leaf($cat61_30->id, '02', 'ກ່ອນອອກບຳນານ', 1);

            // 61.40: ເງິນນະໂຍບາຍພະນັກງານເຮັດວຽກເພີ່ມ
            $cat61_40 = $this->node($cat61->id, '40', 'ເງິນນະໂຍບາຍພະນັກງານເຮັດວຽກເພີ່ມ', 3);
                $this->leaf($cat61_40->id, '01', 'ເຮັດວຽກນອກໂມງລັດຖະການ', 1);
                $this->leaf($cat61_40->id, '03', 'ຄົ້ນຄວ້າ ແລະ ວິໄຈ', 2, 'direct');
                $this->leaf($cat61_40->id, '04', 'ຂຽນບົດ ແລະ ຮຽບຮຽງ', 3, 'direct');
                $this->leaf($cat61_40->id, '05', 'ສອນພິເສດ', 4, 'direct');
                $this->leaf($cat61_40->id, '06', 'ຄ່າເວັນຍາມ (ປ້ອງກັນ)', 5);

            // 61.50: ເບ້ຍລ້ຽງນັກຮຽນພາຍໃນ-ຕ່າງປະເທດ
            $cat61_50 = $this->node($cat61->id, '50', 'ເບ້ຍລ້ຽງນັກຮຽນພາຍໃນ-ຕ່າງປະເທດ', 4);

                // 61.50.01: ນັກຮຽນພາຍໃນ
                $cat61_50_01 = $this->node($cat61_50->id, '01', 'ນັກຮຽນພາຍໃນ', 1);
                    $this->leaf($cat61_50_01->id, '03', 'ເບ້ຍລ້ຽງນັກຮຽນຊັ້ນສູງ, ປະລິນຍາຕີ', 1);
                    $this->leaf($cat61_50_01->id, '04', 'ເບ້ຍລ້ຽງເດັກກຳພ້າ ແລະ ຊົນເຜົ່າ', 2);

                // 61.50.03: ເບ້ຍລ້ຽງໃຫ້ນັກຮຽນພາຍໃນຝຶກງານ (one-time payment)
                $cat61_50_03 = $this->node($cat61_50->id, '03', 'ເບ້ຍລ້ຽງໃຫ້ນັກຮຽນພາຍໃນຝຶກງານ', 2);
                    $this->leaf($cat61_50_03->id, '01', 'ຄ່າອັດຕາກິນນັກຮຽນພາຍໃນຝຶກງານ', 1, 'x1');
                    $this->leaf($cat61_50_03->id, '02', 'ຄ່າເດີນທາງນັກຮຽນພາຍໃນຝຶກງານ', 2, 'x1');

            // 61.80: ຊ່ວຍເຫຼືອດ້ານສັງຄົມ
            $cat61_80 = $this->node($cat61->id, '80', 'ຊ່ວຍເຫຼືອດ້ານສັງຄົມ', 5);
                $this->leaf($cat61_80->id, '02', 'ເງິນນະໂຍບາຍປ່ວຍໂປ່ວພະຍາດ', 1);
    }

    private function node(?int $parentId, string $code, string $name, int $sort): SalaryBudgetCode
    {
        return SalaryBudgetCode::create([
            'parent_id'   => $parentId,
            'code'        => $code,
            'name'        => $name,
            'sort_order'  => $sort,
            'is_leaf'     => false,
            'annual_mode' => 'x12',
        ]);
    }

    private function leaf(int $parentId, string $code, string $name, int $sort, string $mode = 'x12'): SalaryBudgetCode
    {
        return SalaryBudgetCode::create([
            'parent_id'   => $parentId,
            'code'        => $code,
            'name'        => $name,
            'sort_order'  => $sort,
            'is_leaf'     => true,
            'annual_mode' => $mode,
        ]);
    }
}
