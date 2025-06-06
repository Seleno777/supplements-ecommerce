import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    stock: number;
    category: string | null;
    user: {
        name: string;
    };
}

export type CartItem = {
    id: number;
    product_id: number;
    quantity: number;
    user_id: number;
    product: {
        id: number;
        name: string;
        price: number;
        stock: number;
        description: string;
    };
};

export type BreadcrumbItemType = BreadcrumbItem;
