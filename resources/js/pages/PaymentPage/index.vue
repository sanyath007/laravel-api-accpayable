<template>
  <div class="container-fluid">
    <router-view></router-view>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'PaymentPage',
  data () {
    return {

    }
  },
  mounted () {
    this.$store.dispatch('page/setCurrentPage', this.$route.name)
  },
  computed: {
    ...mapGetters({
      token: 'user/getToken'
    })
  }
}
</script>

<style scoped>

</style>
