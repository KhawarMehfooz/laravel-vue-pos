<script setup lang="ts">
import { Head, usePage, router } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { ref } from 'vue';
import { useToast } from "primevue/usetoast";
const toast = useToast();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'App settings',
        href: '/settings/app',
    },
];

const { props } = usePage()

const settings = ref(props.settings);

const formData = ref({
    'business_name': settings.value.business_name || '',
    'business_location': settings.value.business_location || '',
    'business_contact': settings.value.business_contact || null,
    'business_email': settings.value.business_email || '',
    'business_logo': settings.value.business_logo || null,
    'currency_symbol': settings.value.currency_symbol || '',
    'stock_source_label': settings.value.stock_source_label || '',
})
const previewLogo = ref<string | null>(settings.value.business_logo ? '/storage/' + settings.value.business_logo : null);

const saveSettings = () => {
    if (
        !formData.value.business_name.trim() ||
        !formData.value.business_location.trim() ||
        !formData.value.currency_symbol.trim() ||
        !formData.value.stock_source_label.trim()
    ) {
        toast.add({ severity: 'info', summary: 'Fill Required Fields', detail: 'Business Name, Location, Currency Symbol, and Stock Source Label are required.', life: 3000 });
        return;
    }

    const form = new FormData();
    form.append('business_name', formData.value.business_name);
    form.append('business_location', formData.value.business_location);
    form.append('business_contact', formData.value.business_contact || '');
    form.append('business_email', formData.value.business_email || '');
    form.append('currency_symbol', formData.value.currency_symbol);
    form.append('stock_source_label', formData.value.stock_source_label);

    if (formData.value.business_logo instanceof File) {
        form.append('business_logo', formData.value.business_logo);
    }

    router.post('/settings/app', form, {
        forceFormData: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Settings Saved Successfully', life: 3000 });
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to Save Settings', life: 3000 });
        }
    });
};

const onFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input?.files?.[0]) {
        const file = input.files[0];
        // Create a preview URL for the selected file
        previewLogo.value = URL.createObjectURL(file);

        formData.value.business_logo = file;
    }
};


</script>

<template>
    <Toast />
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="App settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="App settings" description="Update your application settings." />
                <hr>
                <form @submit.prevent="saveSettings">
                    <div class="grid grid-cols-1 md:grid-cols-2 items-center gap-4">
                        <div class="w-full">
                            <label class="block mb-2" for="business_name">Business Name</label>
                            <InputText v-model="formData.business_name" required class="w-full" size="small"
                                id="business_name" />
                        </div>
                        <div class="w-full">
                            <label class="block mb-2" for="business_location">Business Location</label>
                            <InputText v-model="formData.business_location" required class="w-full" size="small"
                                id="business_location" />
                        </div>

                        <div class="w-full">
                            <label class="block mb-2" for="business_contact">Business Contact</label>
                            <InputMask v-model="formData.business_contact" class="w-full" size="small"
                                id="business_contact" mask="9999-9999999" placeholder="9999-9999999" />
                        </div>
                        <div class="w-full">
                            <label class="block mb-2" for="business_email">Business Email</label>
                            <InputText v-model="formData.business_email" type="email" class="w-full" size="small"
                                id="business_email" />
                        </div>

                        <div class="w-full">
                            <label class="block mb-2" for="business_logo">Logo</label>
                            <input type="file" class="w-full" id="business_logo" @change="onFileChange" />
                        </div>
                        <div class="w-full">
                            <label for="">Logo Preview</label>
                            <img v-if="previewLogo" :src="previewLogo" alt="Logo Preview"
                                class="w-32 h-32 object-contain" />
                        </div>
                        <div class="w-full">
                            <label class="block mb-2" for="currency_symbol">Currency Symbol</label>
                            <InputText v-model="formData.currency_symbol" required class="w-full" size="small"
                                id="currency_symbol" />
                        </div>
                        <div class="w-full">
                            <label class="block mb-2" for="stock_source_label">Stock Source Label</label>
                            <InputText v-model="formData.stock_source_label" required class="w-full" size="small"
                                id="stock_source_label" />
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="w-full ">
                        <Button class="" type="submit" label="Save Settings" />
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
