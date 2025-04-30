<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'App settings',
        href: '/settings/app',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="App settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="App settings" description="Update your application settings." />
                <hr>
                <form class="grid grid-cols-1 md:grid-cols-2 items-center gap-4">
                    <div class="w-full">
                        <label class="block mb-2" for="business_name">Business Name</label>
                        <InputText class="w-full" size="small" id="business_name" />
                    </div>
                    <div class="w-full">
                        <label class="block mb-2" for="business_location">Business Location</label>
                        <InputText class="w-full" size="small" id="business_location" />
                    </div>

                    <div class="w-full">
                        <label class="block mb-2" for="business_contact">Business Contact</label>
                        <InputMask class="w-full" size="small" id="business_contact" mask="9999-9999999" placeholder="9999-9999999" />
                    </div>
                    <div class="w-full">
                        <label class="block mb-2" for="business_email">Business Email</label>
                        <InputText type="email" class="w-full" size="small" id="business_email" />
                    </div>

                    <div class="w-full">
                        <label class="block mb-2" for="business_logo">Logo</label>
                        <InputText type="file" class="w-full" size="small" id="business_contact" />
                    </div>
                    <div class="w-full">
                        <label class="block mb-2" for="currency_symbol">Currency Symbol</label>
                        <InputText class="w-full" size="small" id="currency_symbol" />
                    </div>
                    <div class="w-full">
                        <label class="block mb-2" for="stock_source_label">Stock Source Label</label>
                        <InputText class="w-full" size="small" id="stock_source_label" />
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
