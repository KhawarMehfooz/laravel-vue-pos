<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useConfirm } from 'primevue/useconfirm';
import { debounce } from 'lodash';
import { Head, usePage, router } from '@inertiajs/vue3';

const confirm = useConfirm()

const { props } = usePage()
const companies = ref(props.companies || []);
const searchQuery = ref('')

const pagination = ref(props.pagination || [])
const rows = ref(pagination.value.perPage || 10)
const page = ref(pagination.value.currentPage || 1)

const createCompanyDialogueVisible = ref(false)

const formData = ref({
    name: '',
    email: '',
    phone_number: '',
    website: ''
})

const selectedCompany = ref(null)

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
        title: 'Companies',
        href: '/companies',
    },
];

const onSearchChange = debounce(() => {
    fetchCompanies();
}, 500);

const openCreateCompanyModal = () => {
    // Clear the formData (reset to empty)
    formData.value = {
        name: '',
        email: '',
        phone_number: '',
        website: ''
    };
    selectedCompany.value = null;
    createCompanyDialogueVisible.value = true;
};

const openEditCompanyModal = (company) => {
    // Set formData values based on the selected company
    formData.value = {
        name: company.name,
        email: company.email || '',  // Optional fields, set to empty if not available
        phone_number: company.phone_number || '',
        website: company.website || ''
    };
    selectedCompany.value = company;
    createCompanyDialogueVisible.value = true;
};

const fetchCompanies = (paginationState = { page: page.value, rows: rows.value }) => {
    router.get('/companies', {
        page: paginationState.page,
        rows: paginationState.rows,
        search: searchQuery.value
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            companies.value = page.props.companies;
            pagination.value = page.props.pagination;
        }
    });
};

const onPageChange = (event) => {
    // Update the current page
    page.value = event.page + 1;  // event.page starts from 0, so we add 1
    fetchCompanies({ page: page.value, rows: rows.value });
};

const onRowsPerPageChange = (event) => {
    // Update the rows per page and reset to the first page
    rows.value = event;
    page.value = 1;  // Reset to the first page when rows change
    fetchCompanies({ page: page.value, rows: rows.value });
};

const saveCompany = () => {
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
    createCompanyDialogueVisible.value = false;

    if (selectedCompany.value) {
        // Update existing company
        console.log('Updating company:', selectedCompany.value.id, companyData);
        
        router.put(`/companies/${selectedCompany.value.id}`, companyData, {
            onSuccess: () => {
                // Reset selectedCompany after successful update
                selectedCompany.value = null;
                // Refetch with current pagination state
                fetchCompanies(currentPaginationState);
                resetForm()
            }
        });
    } else {
        // Create new company
        router.post('/companies', companyData, {
            onSuccess: () => {
                resetForm()
                // Refetch with current pagination state
                fetchCompanies(currentPaginationState);
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

            router.delete(`/companies/${company.id}`, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    fetchCompanies(currentPaginationState);
                }
            });
        },
        position: 'top'
    });
};
</script>

<template>
    <Head title="Companies" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="p-4">
            <DataTable :value="companies" show-gridlines paginator :lazy="true" :rows="pagination.perPage"
                :first="(pagination.currentPage - 1) * pagination.perPage" :totalRecords="pagination.total"
                :rowsPerPageOptions="[10, 50, 100]" @page="onPageChange" @update:rows="onRowsPerPageChange"
                paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                currentPageReportTemplate="{first} to {last} of {totalRecords}" size="small">
                <template #header>
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold">Companies</h2>
                        <div class="flex items-center gap-4">
                            <Button icon="pi pi-plus" size="small" label="Add New" @click="openCreateCompanyModal" />
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
                <Column field="name" header="Company Name" />
                <Column field="email" header="Email">
                    <template #body="slotProps">
                        <span>{{ slotProps.data.email || 'N/A' }}</span>
                    </template>
                </Column>
                <Column field="phone_number" header="Phone Number">
                    <template #body="slotProps">
                        <span>{{ slotProps.data.phone_number || 'N/A' }}</span>
                    </template>
                </Column>
                <Column field="website" header="Website">
                    <template #body="slotProps">
                        <span>{{ slotProps.data.website || 'N/A' }}</span>
                    </template>
                </Column>
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
                <template #empty>No Companies Found...</template>
            </DataTable>

            <!-- Create Company Dialog -->
            <Dialog v-model:visible="createCompanyDialogueVisible" modal
                :header="selectedCompany ? 'Edit Company' : 'Create Company'" :style="{ width: '40rem' }"
                position="top">
                <form @submit.prevent="saveCompany">
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="CompanyName">Company Name</label>
                            <InputText v-model="formData.name" required size="small" class="w-full" id="CompanyName"
                                autocomplete="off" />
                        </div>
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="companyEmail">Company Email</label>
                            <InputText v-model="formData.email" size="small" class="w-full" id="companyEmail"
                                autocomplete="off" />
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="CompanyPhoneNumber">Company Phone Number</label>
                            <InputText v-model="formData.phone_number" size="small" class="w-full"
                                id="CompanyPhoneNumber" autocomplete="off" />
                        </div>
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="companyWebsite">Company Website</label>
                            <InputText v-model="formData.website" size="small" class="w-full" id="companyWebsite"
                                autocomplete="off" />
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="flex items-center justify-end gap-4">
                        <Button size="small" label="Cancel" severity="secondary"
                            @click="createCompanyDialogueVisible = false" />
                        <Button type="submit" size="small" label="Save" />
                    </div>
                </form>
            </Dialog>

            <ConfirmDialog />
        </div>
    </AppLayout>
</template>