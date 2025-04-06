<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

import { Head, usePage, router } from '@inertiajs/vue3';


const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Categories',
        href: '/categories',
    },
];

const { props } = usePage()
const categories = ref(props.categories || []);

const createCategoryDialogueVisible = ref(false)
const categoryName = ref('')

const createCategory = ()=>{
    if(!categoryName.value.trim()){
        alert('Category name is required!')
        return;
    }

    router.post('/categories',{name: categoryName.value},{
        onSuccess: ()=>{
            categoryName.value = ''
            createCategoryDialogueVisible.value = false
        }
    })
}
</script>

<template>

    <Head title="Categories" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="p-4">
            <DataTable class="" :value="categories" show-gridlines>
                <template #header>
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold">Categories</h2>
                        <div class="flex items-center gap-4">
                            <Button icon="pi pi-plus" size="small" label="Add New"
                                @click="createCategoryDialogueVisible = true" />
                            <IconField>
                                <InputIcon>
                                    <i class="pi pi-search" />
                                </InputIcon>
                                <InputText size="small" placeholder="Search..." />
                            </IconField>
                        </div>
                    </div>

                </template>
                <Column field="name" header="Category Name" />
                <Column header="Actions" />

                <template #empty>No Cateogories Found...</template>

            </DataTable>

            <!-- Create Category Dialog -->
            <Dialog v-model:visible="createCategoryDialogueVisible" modal header="Create Category"
                :style="{ width: '30rem' }" position="top">
                <form @submit.prevent="createCategory">
                    <div class="flex flex-col gap-2 mb-2">
                        <label for="categoryName">Category Name</label>
                        <InputText v-model="categoryName" required size="small" id="categoryName" autocomplete="off" />
                    </div>
                    <div class="flex items-center justify-end gap-4">
                        <Button size="small" label="Cancel" severity="secondary" @click="createCategoryDialogueVisible = false" />
                        <Button type="submit" size="small" label="Save" />
                    </div>
                </form>

            </Dialog>
        </div>

    </AppLayout>
</template>