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
} from 'lucide-vue-next';
import * as BillingBalanceController from '@/actions/App/Http/Controllers/BillingBalanceController';
import * as BillingClosingController from '@/actions/App/Http/Controllers/BillingClosingController';
import * as PaymentController from '@/actions/App/Http/Controllers/PaymentController';
import * as PurchaseController from '@/actions/App/Http/Controllers/PurchaseController';
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
        title: '請求締め処理',
        href: BillingClosingController.index.url(),
        icon: ClipboardList,
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
                title: '所属マスタ',
                href: DeptController.index.url(),
                icon: Building2,
            },
            {
                title: '社員マスタ',
                href: EmployeeController.index.url(),
                icon: Users,
            },
            {
                title: '得意先マスタ',
                href: CustomerController.index.url(),
                icon: Building,
            },
            {
                title: '仕入先マスタ',
                href: SupplierController.index.url(),
                icon: Building2,
            },
            {
                title: '商品マスタ',
                href: ProductController.index.url(),
                icon: Package,
            },
            {
                title: '車両マスタ',
                href: VehicleController.index.url(),
                icon: Car,
            },
            {
                title: '車両機種マスタ',
                href: VehicleModelController.index.url(),
                icon: Bike,
            },
            {
                title: '請求残高マスタ',
                href: BillingBalanceController.index.url(),
                icon: Receipt,
            },
            {
                title: '倉庫マスタ',
                href: WarehouseController.index.url(),
                icon: Warehouse,
            },
            {
                title: '在庫残高マスタ',
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
