<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2,
    FolderGit2,
    LayoutGrid,
    Users,
    Building,
    Package,
    Settings2,
    FileText,
    Banknote,
    Bike,
    Car,
    ShoppingCart,
    Receipt,
    Warehouse,
    BoxesIcon,
    ClipboardList,
    SlidersHorizontal,
    Upload,
} from 'lucide-vue-next';
import * as BillingBalanceController from '@/actions/App/Http/Controllers/BillingBalanceController';
import * as BillingClosingController from '@/actions/App/Http/Controllers/BillingClosingController';
import * as PartsSaleImportController from '@/actions/App/Http/Controllers/PartsSaleImportController';
import * as MonthlyClosingController from '@/actions/App/Http/Controllers/MonthlyClosingController';
import * as PaymentController from '@/actions/App/Http/Controllers/PaymentController';
import * as PurchaseController from '@/actions/App/Http/Controllers/PurchaseController';
import * as SystemSettingController from '@/actions/App/Http/Controllers/SystemSettingController';
import * as InventoryBalanceController from '@/actions/App/Http/Controllers/InventoryBalanceController';
import * as SupplierController from '@/actions/App/Http/Controllers/SupplierController';
import * as WarehouseController from '@/actions/App/Http/Controllers/WarehouseController';
import * as CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import * as DeptController from '@/actions/App/Http/Controllers/DeptController';
import * as EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import * as ProductController from '@/actions/App/Http/Controllers/ProductController';
import * as QuoteController from '@/actions/App/Http/Controllers/QuoteController';
import * as SaleController from '@/actions/App/Http/Controllers/SaleController';
import * as VehicleController from '@/actions/App/Http/Controllers/VehicleController';
import * as VehicleModelController from '@/actions/App/Http/Controllers/VehicleModelController';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: '売上',
        href: SaleController.index.url(),
        icon: ShoppingCart,
    },
    {
        title: '入金',
        href: PaymentController.index.url(),
        icon: Banknote,
    },
    {
        title: '仕入',
        href: PurchaseController.index.url(),
        icon: ShoppingCart,
    },
    {
        title: '請求管理',
        href: BillingClosingController.index.url(),
        icon: ClipboardList,
    },
    {
        title: '月次繰越',
        href: MonthlyClosingController.index.url(),
        icon: ClipboardList,
    },
    {
        title: '売上取込',
        href: PartsSaleImportController.index.url(),
        icon: Upload,
    },
    {
        title: '見積',
        href: QuoteController.index.url(),
        icon: FileText,
    },
    {
        title: '設定',
        icon: Settings2,
        children: [
            {
                title: '設定',
                href: SystemSettingController.show.url(),
                icon: SlidersHorizontal,
            },
            {
                title: '所属',
                href: DeptController.index.url(),
                icon: Building2,
            },
            {
                title: '社員',
                href: EmployeeController.index.url(),
                icon: Users,
            },
            {
                title: '得意先',
                href: CustomerController.index.url(),
                icon: Building,
            },
            {
                title: '仕入先',
                href: SupplierController.index.url(),
                icon: Building2,
            },
            {
                title: '商品',
                href: ProductController.index.url(),
                icon: Package,
            },
            {
                title: '車両品番',
                href: VehicleController.index.url(),
                icon: Car,
            },
            {
                title: '機種商品',
                href: VehicleModelController.index.url(),
                icon: Bike,
            },
            {
                title: '請求残高',
                href: BillingBalanceController.index.url(),
                icon: Receipt,
            },
            {
                title: '倉庫',
                href: WarehouseController.index.url(),
                icon: Warehouse,
            },
            {
                title: '在庫残高',
                href: InventoryBalanceController.index.url(),
                icon: BoxesIcon,
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
