<template>
  <div class="container-fluid">
    <!-- Page header -->
    <breadcrumb :pageTitle="'รายการหนี้'" />

    <loading :active.sync="isLoading" 
      :can-cancel="true"
      :is-full-page="true"
    />

    <div class="row mb-2">
      <div class="col-md-6">
        <h3>รายการรับหนี้</h3>
      </div>
      <div class="col-md-6 text-right">
        <v-btn class="mx-2" fab dark color="indigo" @click="showModal">
          <v-icon dark>mdi-plus</v-icon>
        </v-btn>
      </div>
    </div>
    <!-- Page header -->

    <!-- Search Box -->
    <search-box @onSearch="handleSearch" />
    
    <!-- Tab Lists -->
    <nav class="mt-2">
      <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a
          class="nav-item nav-link active"
          id="nav-debt-tab"
          data-toggle="tab"
          href="#nav-debt"
          role="tab"
          aria-controls="nav-debt"
          aria-selected="true"
        >
          รายการรอดำเนินการ
        </a>
        <a
          class="nav-item nav-link"
          id="nav-approve-tab"
          data-toggle="tab"
          href="#nav-approve"
          role="tab"
          aria-controls="nav-approve"
          aria-selected="true"
        >
          รายการขออนุมัติจ่ายหนี้
        </a>
        <a
          class="nav-item nav-link"
          id="nav-payment-tab"
          data-toggle="tab"
          href="#nav-payment"
          role="tab"
          aria-controls="nav-payment"
          aria-selected="true"
        >
          รายการตัดจ่ายหนี้
        </a>
        <a 
          class="nav-item nav-link" 
          id="nav-setzero-tab" 
          data-toggle="tab" 
          href="#nav-setzero" 
          role="tab" 
          aria-controls="nav-setzero" 
          aria-selected="true"
        >
          รายการลดหนี้ศูนย์
        </a>
      </div>
    </nav>

    <div class="tab-content" id="nav-tabContent">
      <div class="tab-pane fade show active" id="nav-debt" role="tabpanel" aria-labelledby="nav-debt-tab">
        <debt-list :lists="debts" :actions="true" />
      </div><!-- /.tab-pane -->
      <div class="tab-pane fade show" id="nav-approve" role="tabpanel" aria-labelledby="nav-approve-tab">
        <approved-list :lists="apps" :actions="false" />
      </div><!-- /.tab-pane -->
      <div class="tab-pane fade show" id="nav-payment" role="tabpanel" aria-labelledby="nav-payment-tab">
        <paid-list :lists="paids" :actions="false" />
      </div><!-- /.tab-pane -->
      <div class="tab-pane fade show" id="nav-setzero" role="tabpanel" aria-labelledby="nav-setzero-tab">
        <setzero-list :lists="setZeros" :actions="false" />
      </div><!-- /.tab-pane -->
    </div><!-- /.tab-content -->

    <debt-form :editDebt="debt" :supplierSelected="supplier" v-if="supplier" />
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

import Breadcrumb from '../../components/Breadcrumb'
import SearchBox from '../../components/SearchBox'
import DebtList from '../../components/debt/List'
import ApprovedList from '../../components/debt/ApprovedList'
import PaidList from '../../components/debt/PaidList'
import SetzeroList from '../../components/debt/SetzeroList'
import DebtForm from '../../components/debt/Form'

// Import component
import Loading from 'vue-loading-overlay';
// Import stylesheet
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
  name: 'DebtPage',
  components: {
    'breadcrumb': Breadcrumb,
    'search-box': SearchBox,
    'debt-list': DebtList,
    'approved-list': ApprovedList,
    'paid-list': PaidList,
    'setzero-list': SetzeroList,
    'debt-form': DebtForm,
    Loading
  },
  data () {
    return {
      supplierSelected: '',
    }
  },
  mounted () {
    this.$store.dispatch('page/setCurrentPage', this.$route.name)
    this.$store.dispatch('creditor/fetchAll')
  },
  computed: {
    ...mapGetters({
      isLoading: 'debt/isLoading',
      token: 'user/getToken',
      debt: 'debt/getById',
      debts: 'debt/getAll',
      apps:'debt/getApproveds',
      paids: 'debt/getPaids',
      setZeros: 'debt/getSetZeros',
      suppliers: 'creditor/getAll',
      supplier: 'creditor/getById'
    })
  },
  methods: {
    handleSearch(searchKeys) {
      this.supplierSelected = searchKeys.supplier

      this.$store.dispatch('debt/fetchAll', {
        url: `/debts/${searchKeys.supplier}/${searchKeys.startDate}/${searchKeys.endDate}/${searchKeys.showAll}`,
        page: searchKeys.page
      })
      
      this.$store.dispatch('creditor/fetchById', this.supplierSelected)
    },
    showModal () {
      if (!this.supplierSelected) {
        this.$bvToast.toast('กรุณาเลือกเจ้าหนี้ก่อน !!', {
          title: 'Error',
          variant: 'danger',
          autoHideDelay: 2000
        })
      } else {
        this.$store.dispatch('creditor/fetchById', this.supplierSelected)

        $('#debtFormModal').modal()
      }
    }
  }
}
</script>

<style scoped>

</style>
