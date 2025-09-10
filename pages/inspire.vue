<template>
  <v-container class="fill-height inspire-bg">
    <v-row align="center" justify="center">
      <v-col cols="12" md="8" lg="6">
        <v-card class="pa-8 text-center" rounded="xl" elevation="10">
          <v-avatar size="80" class="mb-6">
             <img
              src="/v.png"
              alt="Vuetify.js"
            >
          </v-avatar>
          
          <v-icon size="48" color="grey lighten-2">mdi-format-quote-open</v-icon>
          
          <blockquote class="blockquote text-h5 font-weight-light my-4">
            &#8220;First, solve the problem. Then, write the code.&#8221;
          </blockquote>
          
          <div class="text-right grey--text text--darken-1">
            <em>&mdash; John Johnson</em>
          </div>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  name: 'InspirePage'
}
</script>

<style scoped>
.inspire-bg {
  background-color: #F0F2F5;
}

.blockquote {
  line-height: 1.6;
}
</style>