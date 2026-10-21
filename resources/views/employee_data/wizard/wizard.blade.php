<div id="employeeDataWizard" class="form-wizard order-create border-0">
    <ul class="nav nav-wizard">
        <li><a class="nav-link" href="#wizard_Service"><span>1</span></a></li>
        <li><a class="nav-link" href="#wizard_Time"><span>2</span></a></li>
        <li><a class="nav-link" href="#wizard_Details"><span>3</span></a></li>
        <li><a class="nav-link" href="#wizard_Payment"><span>4</span></a></li>
    </ul>
    <div class="tab-content">
        @include('employee_data.wizard.step-1-personal')
        @include('employee_data.wizard.step-2-legal-tax')
        @include('employee_data.wizard.step-3-education')
        @include('employee_data.wizard.step-4-family')
    </div>
</div>
