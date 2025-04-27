<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useConfirm } from 'primevue/useconfirm';
import { debounce } from 'lodash';
import { Head, usePage, router } from '@inertiajs/vue3';

const confirm = useConfirm()

const { props } = usePage()
const products = ref(props.products || []);
const categories = ref(props.categories || [])
const companies = ref(props.companies || [])

const searchQuery = ref('')

const pagination = ref(props.pagination || [])
const rows = ref(pagination.value.perPage || 10)
const page = ref(pagination.value.currentPage || 1)

const createProductDialogVisible = ref(false)

const formData = ref({
    name: '',
    category_id: '',
    company_id: '',
    shelf_number: '',
})

const selectedProduct = ref(null)

onMounted(() => {
    searchQuery.value = props.search || '';
    // Initialize pagination from props
    if (props.pagination) {
        rows.value = props.pagination.perPage;
        page.value = props.pagination.currentPage;
    }
});

watch(() => props.search, (newSearch) => {
    searchQuery.value = newSearch || '';
});

watch(() => props.pagination, (newPagination) => {
    if (newPagination) {
        pagination.value = newPagination;
        // Only update if values are different to avoid loops
        if (page.value !== newPagination.currentPage) {
            page.value = newPagination.currentPage;
        }
        if (rows.value !== newPagination.perPage) {
            rows.value = newPagination.perPage;
        }
    }
}, { deep: true });

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '/products',
    },
];

const onSearchChange = debounce(() => {
    fetchProducts();
}, 500);

const openCreateProductModal = () => {
    // Clear the formData (reset to empty)
    formData.value = {
        name: '',
        category_id: '',
        company_id: '',
        shelf_number: '',
    };
    selectedProduct.value = null;
    createProductDialogVisible.value = true;
};

const openEditProductModal = (product) => {
    formData.value = {
        name: product.name,
        shelf_number: product.shelf_number || '',
        category_id: product.category_id || '',
        company_id: product.company_id || '',
    };
    selectedProduct.value = product;
    createProductDialogVisible.value = true;
};


const fetchProducts = (paginationState = { page: page.value, rows: rows.value }) => {
    router.get('/products', {
        page: paginationState.page,
        rows: paginationState.rows,
        search: searchQuery.value
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            products.value = page.props.products;
            pagination.value = page.props.pagination;
        }
    });
};

const onPageChange = (event) => {
    // Update the current page
    page.value = event.page + 1;  // event.page starts from 0, so we add 1
    fetchProducts({ page: page.value, rows: rows.value });
};

const onRowsPerPageChange = (event) => {
    // Update the rows per page and reset to the first page
    rows.value = event;
    page.value = 1;  // Reset to the first page when rows change
    fetchProducts({ page: page.value, rows: rows.value });
};

const saveProduct = () => {
    if (!formData.value.name.trim()) {
        alert('Company name is required');
        return;
    }

    const companyData = {
        name: formData.value.name,
        email: formData.value.email.trim() || null,
        phone_number: formData.value.phone_number.trim() || null,
        website: formData.value.website.trim() || null
    };

    // Capture current pagination state
    const currentPaginationState = {
        page: page.value,
        rows: rows.value
    };

    // Close dialog first to prevent any issues with form state
    createProductDialogVisible.value = false;

    if (selectedProduct.value) {
        // Update existing company
        router.put(`/products/${selectedProduct.value.id}`, companyData, {
            onSuccess: () => {
                // Reset selectedProduct after successful update
                selectedProduct.value = null;
                // Refetch with current pagination state
                fetchProducts(currentPaginationState);
                resetForm()
            }
        });
    } else {
        // Create new company
        router.post('/products', companyData, {
            onSuccess: () => {
                resetForm()
                // Refetch with current pagination state
                fetchProducts(currentPaginationState);
            }
        });
    }
};

// Helper function to reset the form after success
const resetForm = () => {
    formData.value = {
        name: '',
        email: '',
        phone_number: '',
        website: ''
    };
};

const confirmDelete = (company) => {
    confirm.require({
        message: `Are you sure you want to delete ${company.name} Company?`,
        header: 'Delete Confirmation',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            // Capture the current pagination state
            const currentPaginationState = {
                page: page.value,
                rows: rows.value
            };

            router.delete(`/products/${company.id}`, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    fetchProducts(currentPaginationState);
                }
            });
        },
        position: 'top'
    });
};
</script>

<template>

    <Head title="Products" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="p-4">
            <DataTable :value="products" show-gridlines paginator :lazy="true" :rows="pagination.perPage"
                :first="(pagination.currentPage - 1) * pagination.perPage" :totalRecords="pagination.total"
                :rowsPerPageOptions="[10, 50, 100]" @page="onPageChange" @update:rows="onRowsPerPageChange"
                paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                currentPageReportTemplate="{first} to {last} of {totalRecords}" size="small">
                <template #header>
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold">Products</h2>
                        <div class="flex items-center gap-4">
                            <Button icon="pi pi-plus" size="small" label="Add New" @click="openCreateProductModal" />
                            <IconField>
                                <InputIcon>
                                    <i class="pi pi-search" />
                                </InputIcon>
                                <InputText v-model="searchQuery" size="small" placeholder="Search..."
                                    @input="onSearchChange" />
                            </IconField>
                        </div>
                    </div>
                </template>
                <Column field="name" header="Product Name" />
                <Column field="category.name" header="Category" />
                <Column field="company.name" header="Company" />
                <Column field="shelf_number" header="Shelf" />

                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex items-center gap-2">
                            <Button icon="pi pi-pencil" outlined size="small" severity="info"
                                @click="openEditCompanyModal(slotProps.data)" />
                            <Button icon="pi pi-trash" outlined size="small" severity="danger"
                                @click="confirmDelete(slotProps.data)" />
                        </div>
                    </template>
                </Column>
                <template #empty>No products Found...</template>
            </DataTable>

            <!-- Create Company Dialog -->
            <Dialog v-model:visible="createProductDialogVisible" modal
                :header="selectedProduct ? 'Edit Company' : 'Create Company'" :style="{ width: '40rem' }"
                position="top">
                <form @submit.prevent="saveProduct">
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="productName">Product Name</label>
                            <InputText v-model="formData.name" required size="small" class="w-full" id="productName"
                                autocomplete="off" />
                        </div>
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="productCategory">Category</label>
                            <Select id="productCategory" v-model="formData.category_id" size="small"
                                :options="categories" optionLabel="name" optionValue="id"
                                placeholder="Select a Category" class="w-full" />
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="company_id">Company</label>
                            <Select id="company_id" v-model="formData.company_id" size="small"
                                :options="companies" optionLabel="name" optionValue="id" placeholder="Select a Company"
                                class="w-full" />
                        </div>
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="shelf_number">Shelf Number</label>
                            <InputText v-model="formData.shelf_number" required size="small" class="w-full"
                                id="shelf_number" autocomplete="off" />
                        </div>
                    </div>


                    <hr class="my-4">
                    <div class="flex items-center justify-end gap-4">
                        <Button size="small" label="Cancel" severity="secondary"
                            @click="createProductDialogVisible = false" />
                        <Button type="submit" size="small" label="Save" />
                    </div>
                </form>
            </Dialog>

            <ConfirmDialog />
        </div>
    </AppLayout>
</template>