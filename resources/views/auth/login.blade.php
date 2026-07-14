<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- <div class="flex w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0"> --}}
    <div class="flex w-full relative overflow-hidden transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">

    <x-dot-pattern />

        <main class="grid grid-cols-1 lg:grid-cols-3 w-full min-h-screen">

            <!-- Kolom kiri -->
            <div class="hidden lg:flex items-center justify-center">
                <img src={{ asset('images/welcome_image1.png') }} alt="Welcome-Image"
                    class="w-full h-full object-cover object-center" />
            </div>

            <!-- Kolom kanan -->
            <div class="flex bg-white rounded-xl lg:col-span-2 justify-center items-center">
                <div class="min-w-[650px] px-32">

                    <!-- Welcome message -->
                    <div class="flex justify-center p-2">
                        <h1 class="font-black text-4xl">Welcome back to</h1>
                        <img src="{{ asset('images/web_logo.png') }}" alt="website logo" class="ml-2 w-auto h-10"/>
                    </div>
                    
                    <p class="text-2sm text-secondarytext text-center mb-8">Good management transforms complexity into clarity, <br> helping you stay focused and deliver with confidence. </p>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <x-input-label for="email">
                                Email <span class="text-red-500">*</span>
                            </x-input-label>

                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                :value="old('email')" required autofocus autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="mt-4">
                            <x-input-label for="password">
                                Password <span class="text-red-500">*</span>
                            </x-input-label>

                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                                autocomplete="current-password" />

                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-12">
                            <x-primary-button class="w-full justify-center h-10">
                                {{ __('Log in') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</x-guest-layout>
