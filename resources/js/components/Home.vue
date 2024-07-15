<template>
  <div class="flex flex-col min-h-screen bg-cover bg-center bg-no-repeat" style="background-image: url('/images/background.jpg');">
    <div class="flex-grow flex flex-col items-center justify-center transition-all duration-900 ease-in-out" 
         :class="{'mt-8': cakeDays.length}">
      <h1 class="text-2xl font-bold mb-4 text-center">Developer Birthday Cakes</h1>

      <!-- File Upload Section -->
      <div class="mb-8 text-center">
        <h2 class="text-xl font-semibold mb-2">Upload Developers File</h2>
        <input 
          type="file" 
          @change="handleFileUpload" 
          accept=".txt"
          class="mb-2"
        >
        <button 
          @click="uploadFile" 
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        >
          Upload
        </button>
      </div>
    </div>

    <!-- Cake Days Display Section -->
    <div v-if="cakeDays.length" class="w-full max-w-4xl mx-auto px-4 mb-8 flex flex-col">
      <h2 class="text-xl font-semibold mb-2">Cake Days</h2>
      <div class="overflow-x-auto flex-grow" style="height: 400px;">
        <table class="w-full border-collapse border border-gray-300">
          <thead class="sticky top-0 bg-gray-100">
            <tr class="bg-gray-100">
              <th class="border border-gray-300 px-4 py-2">Date</th>
              <th class="border border-gray-300 px-4 py-2">Small Cakes</th>
              <th class="border border-gray-300 px-4 py-2">Large Cakes</th>
              <th class="border border-gray-300 px-4 py-2">People Getting Cake</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="day in paginatedCakeDays" :key="day.date" class="hover:bg-gray-200 bg-gray-50">
              <td class="border border-gray-300 px-4 py-2">{{ day.date }}</td>
              <td class="border border-gray-300 px-4 py-2">{{ day.smallCakes }}</td>
              <td class="border border-gray-300 px-4 py-2">{{ day.largeCakes }}</td>
              <td class="border border-gray-300 px-4 py-2">{{ day.people.join(', ') }}</td>
            </tr>
          </tbody>
        </table>
        <!-- Pagination controls -->
        <div class="mt-4 flex justify-center items-center space-x-2">
          <button @click="prevPage" :disabled="currentPage === 1" 
                  class="px-3 py-1 bg-blue-500 text-white rounded disabled:opacity-50">
            Previous
          </button>
          <span class="text-white">Page {{ currentPage }} of {{ totalPages }}</span>
          <button @click="nextPage" :disabled="currentPage === totalPages" 
                  class="px-3 py-1 bg-blue-500 text-white rounded disabled:opacity-50">
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
  
  <script>
  import axios from 'axios';
  
  export default {
    data() {
        return {
        file: null,
        cakeDays: [],
        error: null,
        currentPage: 1,
        itemsPerPage: 9, // You can adjust this number
        }
    },
    computed: {
      paginatedCakeDays() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return this.cakeDays.slice(start, end);
      },
      totalPages() {
        return Math.ceil(this.cakeDays.length / this.itemsPerPage);
      }
    },
    methods: {
        handleFileUpload(event) {
        this.file = event.target.files[0];
        },
        async uploadFile() {
        if (!this.file) {
            alert('Please select a file first');
            return;
        }

        const formData = new FormData();
        formData.append('developers', this.file);

        try {
            const response = await axios.post('/api/upload-developers', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
            });
            await this.fetchCakeDays();
        } catch (error) {
            alert('Error uploading file. Please try again.');
        }
        },
        async fetchCakeDays() {
        try {
            const response = await axios.get('/api/cake-days');
            this.cakeDays = response.data;
        } catch (error) {
            this.error = 'Failed to fetch cake days. Please try again.';
        }
        },
        nextPage() {
          if (this.currentPage < this.totalPages) {
            this.currentPage++;
          }
        },
        prevPage() {
          if (this.currentPage > 1) {
            this.currentPage--;
          }
        },
        goToPage(page) {
          if (page >= 1 && page <= this.totalPages) {
            this.currentPage = page;
          }
        }
    },
    mounted() {
        this.fetchCakeDays();
    }
    }
  </script>