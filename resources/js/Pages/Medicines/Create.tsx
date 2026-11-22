import InventoryShell from '../../Components/Inventory/InventoryShell';
import { InventoryNavUrls } from '../../Components/Inventory/InventoryModuleNav';
import MedicineForm from '../../Components/Medicines/MedicineForm';
import MedicineSubNav, { MedicineNavUrls } from '../../Components/Medicines/MedicineSubNav';
import { useTranslation } from '../../hooks/useTranslation';
import { SettingsFormUrls } from '../../types/settings';

export default function CreateMedicine({
    urls,
    formData,
    inventoryNav,
    medicineNav,
}: {
    urls: SettingsFormUrls;
    formData: any;
    inventoryNav: InventoryNavUrls;
    medicineNav: MedicineNavUrls;
}) {
    const { t } = useTranslation();

    return (
        <InventoryShell title={`${t('global.create')} · ${t('global.medicines')}`} active="medicines" urls={inventoryNav}>
            <MedicineSubNav active="medicines" urls={medicineNav} />
            <MedicineForm mode="create" urls={urls} formData={formData} />
        </InventoryShell>
    );
}
