document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('generateInsightBtn');
    if (!button) return;
    button.addEventListener('click', generateInsight);
});

function renderBudgetHealth(data) {

    document.getElementById('budgetHealthTitle').textContent = data.title;
    document.getElementById('budgetHealthSummary').textContent = data.summary;
    const card = document.getElementById('budgetHealthCard');

    card.className = 'rounded-xl p-5 border';

    switch (data.color) {
        case 'green':
            card.classList.add(
                'border-green-200',
                'bg-green-50'
            );
            break;

        case 'yellow':
            card.classList.add(
                'border-yellow-200',
                'bg-yellow-50'
            );
            break;

        case 'red':
            card.classList.add(
                'border-red-200',
                'bg-red-50'
            );
            break;

        default:
            card.classList.add(
                'border-slate-200',
                'bg-slate-50'
            );
    }
}

function renderExecutiveSummary(text) {
    document.getElementById('executiveSummary').textContent = text;
}

function renderFindings(findings) {
    const container = document.getElementById('keyFindings');
    container.innerHTML = '';

    findings.forEach(item => {
        let color = 'green';
        let icon = 'fa-circle-info';

        switch (item.severity.toLowerCase()) {
            case 'high':
                color = 'red';
                icon = 'fa-triangle-exclamation';
                break;

            case 'medium':
                color = 'yellow';
                icon = 'fa-circle-exclamation';
                break;
        }

        container.innerHTML += `
            <div class="flex gap-3 p-4 rounded-xl bg-${color}-50 border border-${color}-100">
                <i class="fas ${icon} text-${color}-500 mt-1"></i>
                <div>
                    <p class="font-semibold text-${color}-700">
                        ${item.title}
                    </p>

                    <p class="text-sm text-slate-600">
                        ${item.description}
                    </p>
                </div>
            </div>
        `;
    });
}

function renderRecommendations(items) {

    const container =document.getElementById('recommendations');

    container.innerHTML = '';
    items.forEach(item => {

        container.innerHTML += `
            <div class="mb-4 last:mb-0">
                <p class="font-semibold text-violet-700">
                    ${item.title}
                </p>

                <p class="text-sm text-slate-600 mt-1 leading-6">
                    ${item.description}
                </p>
            </div>
        `;
    });
}

async function generateInsight() {
    const button = document.getElementById('generateInsightBtn');
    const url = button.dataset.url;
    setLoading(true);

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();

        if (!result.success) {throw new Error(result.message);}

        renderInsight(result.data);
        console.log(result.status);
    } catch (error) {
        console.error(error);
        showError(error.message);
    } finally {
        setLoading(false);
    }
}



function setLoading(isLoading) {
    const button = document.getElementById('generateInsightBtn');
    if (isLoading) {
        button.disabled = true;
        button.innerHTML = `
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Generating...
        `;
    } else {
        button.disabled = false;
        button.innerHTML = `Generate Insight`;
    }
}

function renderInsight(data) { 
    document.getElementById('emptyInsight').classList.add('hidden');
    document.getElementById('insightContent').classList.remove('hidden');

    renderBudgetHealth(data.budget_health);
    renderExecutiveSummary(data.executive_summary);
    renderFindings(data.key_findings);
    renderRecommendations(data.recommendations);
}

function showError(message) {
    document.getElementById('emptyInsight').classList.remove('hidden');
    document.getElementById('insightContent').classList.add('hidden');

    Swal.fire({
        icon: 'error',
        title: 'Generate Insight Failed',
        text: message
    });
}