<script lang="ts">
    import { onMount } from 'svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
    import { Badge } from '@/components/ui/badge';
    import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
    import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
    import { Check, Wrench, Clock, Users, DollarSign, BarChart, Shield, Zap, Car, Phone, Mail, MapPin } from 'lucide-svelte';
    import _ from 'lodash';
    import { Link } from '@inertiajs/svelte';
  
    let { year, auth } = $props();
    let userId = $state(0);

    $effect(() => {
        userId = _.get(auth, 'user.id');
    });

  // Features data
  const features = [
    {
      title: "Digital Job Cards",
      description: "Create, track, and manage job cards digitally. No more paper waste.",
      icon: Wrench,
      color: "text-blue-600"
    },
    {
      title: "Real-time Tracking",
      description: "Monitor job progress in real-time. Get notified when tasks are completed.",
      icon: Clock,
      color: "text-green-600"
    },
    {
      title: "Customer Management",
      description: "Store customer details, vehicle history, and service records in one place.",
      icon: Users,
      color: "text-purple-600"
    },
    {
      title: "Inventory Management",
      description: "Track parts, supplies, and manage stock levels automatically.",
      icon: BarChart,
      color: "text-orange-600"
    },
    {
      title: "Invoicing & Payments",
      description: "Generate professional invoices and accept payments digitally.",
      icon: DollarSign,
      color: "text-emerald-600"
    },
    {
      title: "Secure Cloud Storage",
      description: "All your data is securely backed up in the cloud with encryption.",
      icon: Shield,
      color: "text-red-600"
    }
  ];
  
  // Testimonials
  const testimonials = [
    {
      name: "Mike Rodriguez",
      role: "Owner, AutoCare Pro",
      content: "JobCardsPro cut our paperwork by 80%. We're serving 30% more customers with the same staff.",
      avatar: "MR"
    },
    {
      name: "Sarah Chen",
      role: "Manager, QuickFix Garage",
      content: "The real-time updates feature keeps customers happy and reduces phone calls significantly.",
      avatar: "SC"
    },
    {
      name: "James Wilson",
      role: "Mechanic, Wilson's Auto",
      content: "Love the mobile app - can update job status right from the workshop floor.",
      avatar: "JW"
    }
  ];
  
  // Pricing plans
  const pricingPlans = [
    {
      name: "Starter",
      price: "$49",
      period: "/month",
      description: "Perfect for small shops",
      features: [
        "Up to 50 job cards/month",
        "Basic inventory tracking",
        "Customer database",
        "Email support",
        "Mobile app access"
      ]
    },
    {
      name: "Professional",
      price: "$99",
      period: "/month",
      description: "Best for growing businesses",
      popular: true,
      features: [
        "Unlimited job cards",
        "Advanced inventory",
        "Real-time analytics",
        "Priority support",
        "API access",
        "Custom reporting",
        "Team collaboration"
      ]
    },
    {
      name: "Enterprise",
      price: "$199",
      period: "/month",
      description: "For large operations",
      features: [
        "Everything in Pro",
        "Multi-location support",
        "White-label solution",
        "Dedicated account manager",
        "Custom integrations",
        "On-premise deployment",
        "SLA guarantee"
      ]
    }
  ];
  
  // Stats
  const stats = [
    { label: "Auto Shops", value: "500+", icon: Car },
    { label: "Jobs Managed", value: "1M+", icon: Wrench },
    { label: "Customer Satisfaction", value: "98%", icon: Users },
    { label: "Time Saved", value: "40 hrs/week", icon: Clock }
  ];
  
  onMount(() => {
    // Add scroll animations
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-fade-in');
        }
      });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
  });
</script>

<svelte:head>
  <title>JobCardsPro - Auto Shop Management SaaS</title>
  <meta name="description" content="Streamline your auto repair shop operations with JobCardsPro - The complete SaaS solution for job cards, inventory, and customer management.">
  <style>
    @keyframes fade-in {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .animate-fade-in {
      animation: fade-in 0.6s ease-out forwards;
    }
    
    .gradient-text {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .gradient-bg {
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    }
  </style>
</svelte:head>

<div class="min-h-screen">
  <!-- Navigation -->
  <nav class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-sm border-b">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <div class="flex items-center space-x-2">
          <div class="p-2 bg-blue-600 rounded-lg">
            <Wrench class="w-5 h-5 text-white" />
          </div>
          <span class="text-xl font-bold">JobCards<span class="text-blue-600">Pro</span></span>
        </div>

        <!-- Desktop Menu -->
        <div class="hidden md:flex items-center space-x-8">
          <a href="#features" class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Features</a>
          <a href="#how-it-works" class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">How It Works</a>
          <a href="#pricing" class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Pricing</a>
          <a href="#testimonials" class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Testimonials</a>
        </div>

        <!-- CTA Buttons -->
        <div class="flex items-center space-x-4">
            <Link href="/login">
                <Button variant="ghost" class="hidden sm:inline-flex cursor-pointer">
                { userId > 0 ? 'Dashboard': 'Sign In' }
                </Button>
            </Link>
          <Link href="/register">
            <Button class="bg-blue-600 hover:bg-blue-700">Start Free Trial</Button>
          </Link>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 gradient-bg">
    <div class="container mx-auto max-w-7xl">
      <div class="text-center max-w-4xl mx-auto animate-on-scroll">
        <Badge class="mb-6 bg-blue-100 text-blue-700 hover:bg-blue-100">Trusted by 500+ Auto Shops</Badge>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight mb-6">
          Streamline Your Auto Shop with
          <span class="gradient-text">Digital Job Cards</span>
        </h1>
        <p class="text-xl text-gray-600 mb-10 max-w-2xl mx-auto">
          JobCardsPro is the complete SaaS solution that helps auto repair shops manage job cards, inventory, customers, and payments all in one place.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Button size="lg" class="bg-blue-600 hover:bg-blue-700 text-lg px-8 py-6">
            Start 14-Day Free Trial
            <Zap class="ml-2 w-5 h-5" />
          </Button>
          <Button size="lg" variant="outline" class="text-lg px-8 py-6">
            <i class="fas fa-play-circle mr-2"></i>
            Watch Demo
          </Button>
        </div>
      </div>
      
      <!-- Hero Image/Preview -->
      <div class="mt-20 animate-on-scroll">
        <div class="relative mx-auto max-w-5xl">
          <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl opacity-10 blur-xl"></div>
          <div class="relative bg-white rounded-2xl shadow-2xl border p-2">
            <div class="bg-gray-800 rounded-lg p-4 flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                <div class="w-3 h-3 rounded-full bg-green-500"></div>
              </div>
              <div class="text-gray-300 text-sm">JobCardsPro Dashboard</div>
              <div class="w-20"></div>
            </div>
            <div class="grid grid-cols-3 gap-4 p-6">
              <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Active Jobs</div>
                <div class="text-2xl font-bold">24</div>
              </div>
              <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Today's Revenue</div>
                <div class="text-2xl font-bold">$3,450</div>
              </div>
              <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Parts Needed</div>
                <div class="text-2xl font-bold">12</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Stats Section -->
  <section class="py-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
        {#each stats as stat}
          <div class="text-center animate-on-scroll">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 mb-4">
              <stat.icon class="w-6 h-6 text-blue-600" />
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-2">{stat.value}</div>
            <div class="text-gray-600">{stat.label}</div>
          </div>
        {/each}
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-16 animate-on-scroll">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">Everything You Need to Run Your Shop</h2>
        <p class="text-gray-600 text-lg">Powerful features designed specifically for auto repair shops</p>
      </div>
      
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        {#each features as feature, i}
          <Card class="card-hover animate-on-scroll" style="animation-delay: {i * 0.1}s">
            <CardHeader>
              <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg feature-icon mb-4">
                <feature.icon class="w-6 h-6 {feature.color}" />
              </div>
              <CardTitle>{feature.title}</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-gray-600">{feature.description}</p>
            </CardContent>
          </Card>
        {/each}
      </div>
    </div>
  </section>

  <!-- How It Works -->
  <section id="how-it-works" class="py-20">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-16 animate-on-scroll">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">Simple Yet Powerful Workflow</h2>
        <p class="text-gray-600 text-lg">Get started in minutes, see results immediately</p>
      </div>
      
      <div class="max-w-4xl mx-auto">
        <Tabs  class="w-full animate-on-scroll">
          <TabsList class="grid w-full grid-cols-4">
            <TabsTrigger value="step1">1. Create Job</TabsTrigger>
            <TabsTrigger value="step2">2. Assign Tasks</TabsTrigger>
            <TabsTrigger value="step3">3. Track Progress</TabsTrigger>
            <TabsTrigger value="step4">4. Invoice & Close</TabsTrigger>
          </TabsList>
          
          <TabsContent value="step1" class="mt-8">
            <Card>
              <CardHeader>
                <CardTitle>Create Digital Job Card</CardTitle>
                <CardDescription>Scan vehicle VIN or enter manually to create job card in seconds</CardDescription>
              </CardHeader>
              <CardContent class="space-y-2">
                <div class="flex items-center">
                  <Check class="w-5 h-5 text-green-500 mr-2" />
                  <span>Automatic customer lookup</span>
                </div>
                <div class="flex items-center">
                  <Check class="w-5 h-5 text-green-500 mr-2" />
                  <span>Vehicle service history</span>
                </div>
                <div class="flex items-center">
                  <Check class="w-5 h-5 text-green-500 mr-2" />
                  <span>Photo upload capability</span>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
          
          <TabsContent value="step2">
            <Card>
              <CardHeader>
                <CardTitle>Assign Tasks to Team</CardTitle>
                <CardDescription>Break down jobs into tasks and assign to mechanics</CardDescription>
              </CardHeader>
              <CardContent>
                <!-- Content for step 2 -->
              </CardContent>
            </Card>
          </TabsContent>
          
          <!-- Add more tab contents as needed -->
        </Tabs>
      </div>
    </div>
  </section>

  <!-- Pricing Section -->
  <section id="pricing" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-16 animate-on-scroll">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">Simple, Transparent Pricing</h2>
        <p class="text-gray-600 text-lg">Start free, upgrade when you're ready</p>
      </div>
      
      <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
        {#each pricingPlans as plan, i}
          <Card class={`pricing-card ${plan.popular ? 'popular' : ''} animate-on-scroll`} style="animation-delay: {i * 0.2}s">
            {#if plan.popular}
              <div class="popular-badge">Most Popular</div>
            {/if}
            <CardHeader class="text-center">
              <CardTitle>{plan.name}</CardTitle>
              <div class="mt-4">
                <span class="text-4xl font-bold">{plan.price}</span>
                <span class="text-gray-600">{plan.period}</span>
              </div>
              <CardDescription>{plan.description}</CardDescription>
            </CardHeader>
            <CardContent>
              <ul class="space-y-3">
                {#each plan.features as feature}
                  <li class="flex items-center">
                    <Check class="w-5 h-5 text-green-500 mr-3" />
                    <span>{feature}</span>
                  </li>
                {/each}
              </ul>
            </CardContent>
            <CardFooter>
              <Button class={`w-full ${plan.popular ? 'bg-blue-600 hover:bg-blue-700' : ''}`}>
                Get Started
              </Button>
            </CardFooter>
          </Card>
        {/each}
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section id="testimonials" class="py-20">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-16 animate-on-scroll">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">Trusted by Auto Shop Owners</h2>
        <p class="text-gray-600 text-lg">See what our customers say about JobCardsPro</p>
      </div>
      
      <div class="grid md:grid-cols-3 gap-8">
        {#each testimonials as testimonial, i}
          <Card class="animate-on-scroll" style="animation-delay: {i * 0.1}s">
            <CardContent class="pt-6">
              <div class="flex items-center mb-6">
                <Avatar class="h-12 w-12 mr-4">
                  <AvatarFallback>{testimonial.avatar}</AvatarFallback>
                </Avatar>
                <div>
                  <div class="font-semibold">{testimonial.name}</div>
                  <div class="text-sm text-gray-600">{testimonial.role}</div>
                </div>
              </div>
              <p class="text-gray-700 italic">"{testimonial.content}"</p>
            </CardContent>
          </Card>
        {/each}
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="py-20 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto animate-on-scroll">
        <h2 class="text-3xl sm:text-4xl font-bold mb-6">Ready to Transform Your Auto Shop?</h2>
        <p class="text-xl mb-10 text-blue-100">Join thousands of auto shops already using JobCardsPro</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Button size="lg" class="bg-white text-blue-600 hover:bg-gray-100 text-lg px-8 py-6">
            Start Free 14-Day Trial
            <Zap class="ml-2 w-5 h-5" />
          </Button>
          <Button size="lg" variant="outline" class="text-white border-white hover:bg-blue-500 text-lg px-8 py-6">
            Schedule a Demo
          </Button>
        </div>
        <p class="mt-8 text-blue-200">No credit card required • Cancel anytime • Full support included</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-900 text-white py-12">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid md:grid-cols-4 gap-8">
        <div>
          <div class="flex items-center space-x-2 mb-6">
            <div class="p-2 bg-blue-600 rounded-lg">
              <Wrench class="w-5 h-5 text-white" />
            </div>
            <span class="text-xl font-bold">JobCards<span class="text-blue-400">Pro</span></span>
          </div>
          <p class="text-gray-400">Streamlining auto shop operations since 2024</p>
        </div>
        
        <div>
          <h3 class="font-semibold mb-4">Product</h3>
          <ul class="space-y-2 text-gray-400">
            <li><a href="#features" class="hover:text-white">Features</a></li>
            <li><a href="#how-it-works" class="hover:text-white">How It Works</a></li>
            <li><a href="#pricing" class="hover:text-white">Pricing</a></li>
            <li><a href="/" class="hover:text-white">API</a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="font-semibold mb-4">Company</h3>
          <ul class="space-y-2 text-gray-400">
            <li><a href="#/" class="hover:text-white">About</a></li>
            <li><a href="#/" class="hover:text-white">Blog</a></li>
            <li><a href="#/" class="hover:text-white">Careers</a></li>
            <li><a href="#/" class="hover:text-white">Contact</a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="font-semibold mb-4">Contact</h3>
          <ul class="space-y-3 text-gray-400">
            <li class="flex items-center">
              <Mail class="w-4 h-4 mr-2" />
              support@jobcardspro.com
            </li>
            <li class="flex items-center">
              <Phone class="w-4 h-4 mr-2" />
              (555) 123-4567
            </li>
          </ul>
        </div>
      </div>
      
      <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
        <p>© {year} JobCardsPro. All rights reserved.</p>
      </div>
    </div>
  </footer>
</div>