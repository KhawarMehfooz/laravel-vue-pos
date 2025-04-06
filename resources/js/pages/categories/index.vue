<script setup lang="ts">
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useConfirm } from 'primevue/useconfirm';
import { debounce } from 'lodash';

import { Head, usePage, router } from '@inertiajs/vue3';

const confirm = useConfirm()

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Categories',
        href: '/categories',
    },
];

const { props } = usePage()
const categories = ref(props.categories || []);
const searchQuery = ref('')

const pagination = ref(props.pagination || [])
const rows = ref(10)
const page = ref(1)

const createCategoryDialogueVisible = ref(false)
const categoryName = ref('')
const selectedCategory = ref(null)

const onSearchChange = debounce(() => {
    fetchCategories();
}, 500); 

const openCreateCategoryModal = () => {
    categoryName.value = ''
    selectedCategory.value = null
    createCategoryDialogueVisible.value = true
}

const openEditCategoryModal = (category) => {
    categoryName.value = category.name;
    selectedCategory.value = category
    createCategoryDialogueVisible.value = true
}

const fetchCategories = () => {
    router.get('/categories', {
        page: page.value,
        rows: rows.value,
        search: searchQuery.value
    }, {
        onSuccess: (page) => {
            categories.value = page.props.categories;
            pagination.value = page.props.pagination
        }
    });
};

const onPageChange = (event) => {
    console.log(event)
    page.value = event.page + 1;
    fetchCategories();
};

const onRowsPerPageChange = (event) => {
    rows.value = event;
    page.value = 1;
    fetchCategories();
};

watch(rows, () => {
    page.value = 1; 
    fetchCategories();
});

const saveCategory = () => {
    if (!categoryName.value.trim()) {
        alert('Category name is required');
        return;
    }

    if (selectedCategory.value) {
        router.put(`/categories/${selectedCategory.value.id}`, {
            name: categoryName.value
        }, {
            onSuccess: () => {
                fetchCategories();
                categoryName.value = ''
                selectedCategory.value = null
                createCategoryDialogueVisible.value = false
            }
        })
    } else {
        router.post('/categories', { name: categoryName.value }, {
            onSuccess: () => {
                fetchCategories();
                categoryName.value = ''
                createCategoryDialogueVisible.value = false
            }
        })
    }
}

const confirmDelete = (category) => {
    confirm.require({
        message: `Are you sure you want to delete ${category.name} category?`,
        header: 'Delete Confirmation',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            fetchCategories();
            router.delete(`/categories/${category.id}`);
        },
        position: 'top'
    });
};
</script>

<template>

    <Head title="Categories" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="p-4">
            <DataTable :value="categories" show-gridlines paginator :lazy="true" :rows="pagination.perPage"
                :first="(pagination.currentPage - 1) * pagination.perPage" :totalRecords="pagination.total" :rowsPerPageOptions="[10, 50, 100]"
                @page="onPageChange" @update:rows="onRowsPerPageChange"
                paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                currentPageReportTemplate="{first} to {last} of {totalRecords}"
                size="small"
                >
                <template #header>
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold">Categories</h2>
                        <div class="flex items-center gap-4">
                            <Button icon="pi pi-plus" size="small" label="Add New" @click="openCreateCategoryModal" />
                            <IconField>
                                <InputIcon>
                                    <i class="pi pi-search" />
                                </InputIcon>
                                <InputText v-model="searchQuery" size="small" placeholder="Search..." @input="onSearchChange" />
                            </IconField>
                        </div>
                    </div>

                </template>
                <Column field="name" header="Category Name" />

                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex items-center gap-2">
                            <Button icon="pi pi-pencil" outlined size="small" severity="info"
                                @click="openEditCategoryModal(slotProps.data)" />
                            <Button icon="pi pi-trash" outlined size="small" severity="danger"
                                @click="confirmDelete(slotProps.data)" />
                        </div>
                    </template>
                </Column>

                <template #empty>No Cateogories Found...</template>

            </DataTable>

            <!-- Create Category Dialog -->
            <Dialog v-model:visible="createCategoryDialogueVisible" modal
                :header="selectedCategory ? 'Edit Category' : 'Create Category'" :style="{ width: '30rem' }"
                position="top">
                <form @submit.prevent="saveCategory">
                    <div class="flex flex-col gap-2 mb-2">
                        <label for="categoryName">Category Name</label>
                        <InputText v-model="categoryName" required size="small" id="categoryName" autocomplete="off" />
                    </div>
                    <div class="flex items-center justify-end gap-4">
                        <Button size="small" label="Cancel" severity="secondary"
                            @click="createCategoryDialogueVisible = false" />
                        <Button type="submit" size="small" label="Save" />
                    </div>
                </form>
            </Dialog>

            <ConfirmDialog />
        </div>

    </AppLayout>
</template>