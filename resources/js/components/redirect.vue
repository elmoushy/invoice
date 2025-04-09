<template>
</template>

<script>
import { useInvoiceStore } from '../invoice'; 

export default {
    mounted() {
        const invoiceStore = useInvoiceStore();
        invoiceStore.$reset(); 
        this.$router.push('/create-invoice/invoice-details');
        
    }
}
</script>