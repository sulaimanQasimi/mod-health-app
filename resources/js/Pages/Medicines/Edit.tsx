import InventoryShell from '../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../Components/Inventory/InventoryModuleNav';
import MedicineForm from '../../Components/Medicines/MedicineForm';
import MedicineSubNav, { MedicineNavUrls } from '../../Components/Medicines/MedicineSubNav';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function EditMedicine({
    medicine,
    urls,
    formData,
    inventoryNav,
    medicineNav,
}: {
    medicine: any;
    urls: SettingsFormUrls;
    formData: any;
    inventoryNav: InventoryNavUrls;
    medicineNav: MedicineNavUrls;
}) {
    const { t } = useTranslation();

    return (
        <InventoryShell title={`${t('global.edit')} · ${medicine.name}`} active="medicines" urls={inventoryNav}>
            <MedicineSubNav active="medicines" urls={medicineNav} />
            <MedicineForm mode="edit" urls={urls} medicine={medicine} formData={formData} />
        </InventoryShell>
    );
}
