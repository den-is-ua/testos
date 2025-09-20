<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import { computed, onMounted, ref } from "vue";

type Invoice = {
    invoice: string;
    paymentStatus: string;
    totalAmount: string;
    paymentMethod: string;
};

const items = ref<Invoice[]>([]);
const page = ref(1);
const perPage = ref(5);
const total = ref(0);
const lastPage = ref(1);
const loading = ref(false);
const progress = ref(32);

async function fetchInvoices() {
    loading.value = true;
    try {
        const params = new URLSearchParams({ page: String(page.value), per_page: String(perPage.value) });
        const res = await fetch(`/?${params.toString()}`, {
            headers: { 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        items.value = data.data || [];
        total.value = data.total || 0;
        lastPage.value = data.last_page || 1;
    } catch (e) {
        console.error("Failed to load invoices", e);
    } finally {
        loading.value = false;
    }
}

function goTo(p: number) {
    if (p < 1 || p > lastPage.value) return;
    page.value = p;
    fetchInvoices();
}

const pages = computed(() => {
    const arr: number[] = [];
    for (let p = 1; p <= lastPage.value; p++) arr.push(p);
    return arr;
});

onMounted(() => {
    fetchInvoices();
});
</script>

<template>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead class="w-[100px]">
                    Invoice
                </TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Method</TableHead>
                <TableHead class="text-right">
                    Amount
                </TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-if="!loading && items.length === 0">
                <TableCell colspan="4" class="text-center py-6">No invoices found.</TableCell>
            </TableRow>

            <TableRow v-for="invoice in items" :key="invoice.invoice">
                <TableCell class="font-medium">
                    {{ invoice.invoice }}
                </TableCell>
                <TableCell>{{ invoice.paymentStatus }}</TableCell>
                <TableCell>{{ invoice.paymentMethod }}</TableCell>
                <TableCell class="text-right">
                    {{ invoice.totalAmount }}
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>

    <div class="flex items-center w-[100%] justify-center mt-[20px]">
        <div class="flex items-center space-x-2">
            <button class="btn" :disabled="page === 1 || loading" @click="goTo(page - 1)">
                Prev
            </button>

            <button v-for="p in pages" :key="p" :class="['btn', { 'btn-primary': p === page }]" @click="goTo(p)">
                {{ p }}
            </button>

            <button class="btn" :disabled="page === lastPage || loading" @click="goTo(page + 1)">
                Next
            </button>
        </div>
    </div>
</template>